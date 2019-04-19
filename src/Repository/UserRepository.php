<?php

namespace App\Repository;

use App\Attempt\AttemptProviderInterface;
use App\Attempt\Example\ExampleProviderInterface;
use App\DataFixtures\UserFixtures;
use App\DateTime\DateTime as DT;
use App\Entity\Attempt;
use App\Entity\Profile;
use App\Entity\Task;
use App\Entity\User;
use App\Object\ObjectAccessor;
use App\Response\Result\ContractorResult;
use App\Security\User\CurrentUserProviderInterface;
use App\Task\Contractor\ContractorProviderInterface;
use App\Task\Contractor\ContractorResultFactoryInterface;
use App\User\Teacher\TeacherProviderInterface;
use App\User\UserEvaluatorInterface;
use App\Utils\Cache\LocalCache;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Webmozart\Assert\Assert;

final class UserRepository extends ServiceEntityRepository implements TeacherProviderInterface, ContractorProviderInterface, ContractorResultFactoryInterface, UserEvaluatorInterface
{
    private const  SOLVED_EXAMPLES_STANDARDS = [
        1 => [1 => 1, 2 => 5, 3 => 10, 4 => 25, 5 => 50],
        3 => [1 => 1, 2 => 3, 3 => 5, 4 => 15, 5 => 30],
        7 => [1 => 1, 2 => 3, 3 => 5, 4 => 10, 5 => 20],
        90 => [1 => 1, 2 => 3, 3 => 5, 4 => 10, 5 => 15],
    ];

    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    private $localCache;

    /** @var AttemptProviderInterface */
    private $attemptProvider;

    /** @var ExampleProviderInterface */
    private $exampleProvider;

    public function __construct(
        RegistryInterface $registry,
        CurrentUserProviderInterface $currentUserProvider,
        AuthorizationCheckerInterface $authorizationChecker,
        LocalCache $localCache,
        AttemptProviderInterface $attemptProvider,
        ExampleProviderInterface $exampleProvider
    ) {
        parent::__construct($registry, User::class);

        $this->currentUserProvider = $currentUserProvider;
        $this->authorizationChecker = $authorizationChecker;
        $this->localCache = $localCache;
        $this->attemptProvider = $attemptProvider;
        $this->exampleProvider = $exampleProvider;
    }

    private function getAttemptsCount(User $user)
    {
        return $this->createQueryBuilder('u')
            ->select('count(a)')
            ->join('u.sessions', 's')
            ->join('s.attempts', 'a')
            ->where('u = :user')
            ->getQuery()
            ->setParameter('user', $user)
            ->getSingleScalarResult();
    }

    private function getExamplesCount(User $user)
    {
        return $this->createQueryBuilder('u')
            ->select('count(e)')
            ->join('u.sessions', 's')
            ->join('s.attempts', 'a')
            ->join('a.examples', 'e')
            ->where('u = :user')
            ->getQuery()
            ->setParameter('user', $user)
            ->getSingleScalarResult();
    }

    private function getProfilesCount(User $user): int
    {
        return $this->getEntityManager()
            ->getRepository(Profile::class)
            ->countByAuthor($user);
    }

    public function getGuest(): User
    {
        static $user = false;
        $guestUsername = UserFixtures::GUEST_USERNAME;

        if (false === $user) {
            $user = $this->findOneByUsername($guestUsername);
        }

        if (!$user) {
            $user = $this->getNew()
                ->setUsername($guestUsername)
                ->setUsernameCanonical($guestUsername);

            $entityManager = $this->getEntityManager();
            $entityManager->persist($user);
            $entityManager->flush();
        }

        return $user;
    }

    private function getNew(): User
    {
        return ObjectAccessor::initialize(User::class, [
            'enabled' => true,
        ]);
    }

    private function findOneByUloginCredentials($credentials): ?User
    {
        extract($credentials);

        return $this->findOneBy(['network' => $network, 'networkId' => $uid]);
    }

    public function findOneByUloginCredentialsOrNew($credentials): User
    {
        extract($credentials);

        if ($user = $this->findOneByUloginCredentials($credentials)) {
            return $user;
        }

        $uniqUsername = $username;
        $i = 1;

        while ($this->countByUsername($uniqUsername)) {
            $uniqUsername = sprintf('%s-%s', $uniqUsername, $i);
            ++$i;
        }

        $user = $this->getNew()
            ->setUsername($uniqUsername)
            ->setIsSocial(true)
            ->setFirstName($first_name)
            ->setLastName($last_name)
            ->setNetwork($network)
            ->setNetworkId($uid);
        $entityManager = $this->getEntityManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    public function getDoneAttemptsCount(User $user, \DateTimeInterface $dt = null): int
    {
        $localCache = $this->localCache;
        $attempts = $localCache->get(['users[%s].attempts', $user], function () use ($user): array {
            return $this->createQuery('select a from App:Attempt a
join a.session s
join s.user u
where u = :u')
                ->setParameters(['u' => $user])
                ->getResult();
        });

        if ($dt) {
            $attempts = array_filter($attempts, function (Attempt $attempt) use ($dt): bool {
                return $attempt->getAddTime()->getTimestamp() > $dt->getTimestamp();
            });
        }

        $attemptRepository = $this->getEntityRepository(Attempt::class);
        $count = 0;

        foreach ($attempts as $attempt) {
            $isAttemptDone = $localCache->get(['attempts[%s].isDone', $attempt], function () use ($attempt, $attemptRepository): bool {
                return $attemptRepository->isDone($attempt);
            });

            if ($isAttemptDone) {
                ++$count;
            }
        }

        return $count;
    }

    public function getSolvedExamplesCount(User $user, \DateTimeInterface $exampleCreatedAt = null): int
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->select('count(e)')
            ->join('u.sessions', 's')
            ->join('s.attempts', 'a')
            ->join('a.examples', 'e')
            ->where('u = :user')
            ->andWhere('e.isRight = true');
        $parameters = ['user' => $user];

        if (null !== $exampleCreatedAt) {
            $queryBuilder->andWhere('e.addTime > :createdAt');
            $parameters += ['createdAt' => $exampleCreatedAt];
        }

        return $queryBuilder
            ->getQuery()
            ->setParameters($parameters)
            ->getSingleScalarResult();
    }

    public function clearNotEnabledUsers(\DateTimeInterface $dt): int
    {
        $entityManager = $this->getEntityManager();
        $users = $entityManager->createQuery('select u from App:User u
        where u.enabled = false and u.addTime < :dt')
            ->setParameter('dt', \App\DateTime\DateTime::createBySubDays(10))
            ->getResult();

        foreach ($users as $user) {
            $entityManager->remove($user);
        }
        $entityManager->flush();

        return \count($users);
    }

    public function hasExamples(User $user): bool
    {
        return $this->localCache->get(['users[%s].hasExamples', $user], function () use ($user): bool {
            return $this->createQueryBuilder('u')
                ->select('count(e)')
                ->join('u.sessions', 's')
                ->join('s.attempts', 'a')
                ->join('a.examples', 'e')
                ->where('u = :user')
                ->getQuery()
                ->setParameter('user', $user)
                ->getSingleScalarResult();
        });
    }

    public function findByHomework(Task $task): array
    {
        return $this->createQuery('select u from App:User u
            join u.homework h
            where h = :task')
            ->setParameters(['task' => $task])
            ->getResult();
    }

    public function getTeachers(): array
    {
        return $this->findByIsTeacher(true);
    }

    public function getActivityCoefficient(User $user): int
    {
        $assessments = [];

        foreach (self::SOLVED_EXAMPLES_STANDARDS as $lastDays => $standardList) {
            $examplesCount = $this->getAverageRightExamplesCount($lastDays, $user);
            $assessments[] = $this->putAssessment($examplesCount, $standardList);
        }

        return max($assessments);
    }

    private function getAverageRightExamplesCount(int $days, User $user): int
    {
        Assert::greaterThan($days, 0);
        $rightExamplesCount = $this->createQueryBuilder('u')
            ->select('count(e)')
            ->join('u.sessions', 's')
            ->join('s.attempts', 'a')
            ->join('a.examples', 'e')
            ->where('u = :user')
            ->andWhere('e.isRight = true')
            ->andWhere('e.addTime >= :solvedAt')
            ->setParameters([
                'user' => $user,
                'solvedAt' => DT::createBySubDays($days),
            ])
            ->getQuery()
            ->getSingleScalarResult();

        return (int) ($rightExamplesCount / $days);
    }

    private function putAssessment(int $result, array $standardList): int
    {
        $assessment = 0;

        foreach ($standardList as $key => $standard) {
            if ($result >= $standard) {
                $assessment = $key;
            }
        }

        return $assessment;
    }

    public function getSolvedTaskContractors(Task $task): array
    {
        return array_filter($task->getContractors()->toArray(), function (User $contractor) use ($task): bool {
            return $this->isDoneByUser($task, $contractor);
        });
    }

    public function getNotSolvedTaskContractors(Task $task): array
    {
        return array_filter($task->getContractors()->toArray(), function (User $contractor) use ($task): bool {
            return !$this->isDoneByUser($task, $contractor);
        });
    }

    public function getSolvedContractorsCount(Task $task): int
    {
        return \count($this->getSolvedTaskContractors($task));
    }

    private function isDoneByUser(Task $task, User $user): bool
    {
        return $task->getTimesCount() === $this->attemptProvider->getDoneAttemptsCount($task, $user);
    }

    public function isDoneByCurrentContractor(Task $task): bool
    {
        return $this->isDoneByUser($task, $this->currentUserProvider->getCurrentUserOrGuest());
    }

    public function createContractorResult(User $contractor, Task $task): ContractorResult
    {
        return ObjectAccessor::initialize(ContractorResult::class, [
            'contractor' => $contractor,
            'task' => $task,
            'lastAttempt' => $this->attemptProvider->getContractorLastAttempt($contractor, $task),
            'rightExamplesCount' => $this->exampleProvider->getRightExamplesCount($contractor, $task),
            'doneAttemptsCount' => $this->attemptProvider->getDoneAttemptsCount($task, $contractor),
            'rating' => $this->getTaskRating($contractor, $task),
        ]);
    }

    public function createCurrentContractorResult(Task $task): ContractorResult
    {
        return $this->createContractorResult($this->currentUserProvider->getCurrentUserOrGuest(), $task);
    }

    public function mapCreateCurrentContractorResult(array $tasks): array
    {
        return array_map(function (Task $task): ContractorResult {
            return $this->createCurrentContractorResult($task);
        }, $tasks);
    }

    /**
     * @param User $user
     * @param Task $task
     *
     * @return int
     */
    public function getTaskRating(User $user, Task $task): ?int
    {
        $attempts = $this->attemptProvider->getContractorDoneAttempts($user, $task);

        if (empty($attempts)) {
            return null;
        }

        return round(
            array_reduce($attempts, function (float $rating, Attempt $attempt) use ($attempts): float {
                return $rating + $attempt->getResult()->getRating() / \count($attempts);
            }, 0)
        );
    }
}
