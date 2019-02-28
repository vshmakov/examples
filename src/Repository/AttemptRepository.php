<?php

namespace App\Repository;

use App\Entity\Attempt;
use App\Entity\Example;
use App\Entity\Settings;
use App\Entity\Task;
use App\Entity\User;
use App\Object\ObjectAccessor;
use App\Response\AttemptResponse;
use App\Response\AttemptResponseProviderInterface;
use App\Response\ExampleResponse;
use App\Security\User\CurrentUserProviderInterface;
use App\Security\User\CurrentUserSessionProviderInterface;
use App\Service\ExampleManager;
use App\Service\UserLoader;
use App\Utils\Cache\LocalCache;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class AttemptRepository extends ServiceEntityRepository implements AttemptResponseProviderInterface
{
    use BaseTrait;

    private $exampleRepository;
    private $userLoader;
    private $userRepository;

    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    private $localCache;

    /** @var CurrentUserSessionProviderInterface */
    private $currentUserSessionProvider;

    public function __construct(
        RegistryInterface $registry,
        ExampleRepository $exampleRepository,
        UserLoader $userLoader,
        UserRepository $userRepository,
        CurrentUserProviderInterface $currentUserProvider,
        AuthorizationCheckerInterface $authorizationChecker,
        LocalCache $localCache,
        CurrentUserSessionProviderInterface $currentUserSessionProvider
    ) {
        parent::__construct($registry, Attempt::class);

        $this->exampleRepository = $exampleRepository;
        $this->userLoader = $userLoader;
        $this->userRepository = $userRepository;
        $this->currentUserProvider = $currentUserProvider;
        $this->authorizationChecker = $authorizationChecker;
        $this->localCache = $localCache;
        $this->currentUserSessionProvider = $currentUserSessionProvider;
    }

    public function findLastActualByCurrentUser(): ?Attempt
    {
        $attempt = $this->findLastByCurrentUser();

        return null !== $attempt && $this->isActual($attempt) ? $attempt : null;
    }

    private function isActual(Attempt $attempt): bool
    {
        $limitTime = $attempt->getLimitTime();
        $remainedExamplesCount = $this->getRemainedExamplesCount($attempt);

        return 0 < $remainedExamplesCount && time() < $limitTime->getTimestamp();
    }

    public function findLastByCurrentUser(): ?Attempt
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->select('a');

        if (!$this->currentUserProvider->isCurrentUserGuest()) {
            $queryBuilder
                ->join('a.session', 's')
                ->where('s.user = :user');
            $parameters = ['user' => $this->currentUserProvider->getCurrentUserOrGuest()];
        } else {
            $queryBuilder->where('a.session = :session');
            $parameters = ['session' => $this->currentUserSessionProvider->getCurrentUserSessionOrNew()];
        }

        return $queryBuilder
            ->orderBy('a.addTime', 'desc')
            ->getQuery()
            ->setParameters($parameters)
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    public function getTitle(Attempt $attempt): string
    {
        return 'Попытка №'.$this->getNumber($attempt);
    }

    public function getNumber(Attempt $attempt): int
    {
        return $this->createQueryBuilder('a')
            ->select('count(a)')
            ->join('a.session', 's')
            ->where('s.user = :user')
            ->andWhere('a.addTime <= :createdAt')
            ->getQuery()
            ->setParameters([
                'user' => $attempt->getSession()->getUser(),
                'createdAt' => $attempt->getAddTime(),
            ])
            ->getSingleScalarResult();
    }

    public function getFinishTime(Attempt $attempt): \DateTimeInterface
    {
        $finishTime = $this->createQueryBuilder('a')
            ->select('a.answerTime')
            ->join('a.examples', 'e')
            ->where('a = :attempt')
            ->andWhere('e.answerTime is not null')
            ->orderBy('e.answerTime', 'desc')
            ->getQuery()
            ->setParameter('attempt', $attempt)
            ->setMaxResults(1)
            ->getOneOrNullResult();

        return null !== $finishTime ? \DT::createFromDT($finishTime) : $attempt->getAddTime();
    }

    public function getSolvedExamplesCount(Attempt $attempt): int
    {
        if (!$attempt->getSettings()->isDemanding()) {
            return $this->getAnsweredExamplesCount($attempt);
        }

        return $this->createQueryBuilder('a')
            ->select('count(a)')
            ->join('a.examples', 'e')
            ->where('a.isRight = true')
            ->andWhere('a = :attempt')
            ->getQuery()
            ->setParameter('attempt', $attempt)
            ->getSingleScalarResult();
    }

    public function getAnsweredExamplesCount(Attempt $attempt): int
    {
        return $this->createQueryBuilder('a')
            ->select('count(a)')
            ->join('a.examples', 'e')
            ->where('e.answer is not null')
            ->andWhere('a = :attempt')
            ->getQuery()
            ->setParameter('attempt', $attempt)
            ->getSingleScalarResult();
    }

    public function getErrorsCount(Attempt $attempt): int
    {
        return $this->exampleRepository->count([
            'attempt' => $attempt,
            'isRight' => false,
        ]);
    }

    public function getRating(Attempt $attempt): int
    {
        return ExampleManager::rating($attempt->getExamplesCount(), $this->getRongExamplesCount($attempt));
    }

    public function countByCurrentUser(): int
    {
        return $this->createQueryBuilder('a')
            ->select('count(a)')
            ->join('a.session', 's')
            ->join('s.user', 'u')
            ->where('u = :currentUser')
            ->getQuery()
            ->setParameter('currentUser', $this->currentUserProvider->getCurrentUserOrGuest())
            ->getSingleScalarResult();
    }

    public function findAllByCurrentUser(): array
    {
        return $this->createQuery('select a from App:Attempt a
join a.session s
join s.user u
where u = :u
order by a.addTime asc')
            ->setParameter('u', $this->userLoader->getUser())
            ->getResult();
    }

    public function getNewByCurrentUser(): Attempt
    {
        $attempt = $this->createNewByCurrentUser()
            ->setSettings($this->getEntityRepository(Settings::class)->getNewByCurrentUser());

        $entityManager = $this->getEntityManager();
        $entityManager->persist($attempt);
        $entityManager->flush();

        return $attempt;
    }

    public function getNewByCurrentUserAndTask(Task $task): Attempt
    {
        $attempt = $this->createNewByCurrentUser()
            ->setTask($task)
            ->setSettings($task->getSettings());

        $entityManager = $this->getEntityManager();
        $entityManager->persist($attempt);
        $entityManager->flush();

        return $attempt;
    }

    private function createNewByCurrentUser(): Attempt
    {
        $attempt = ObjectAccessor::initialize(Attempt::class, [
            'session' => $this->currentUserSessionProvider->getCurrentUserSessionOrNew(),
        ]);

        return $attempt;
    }

    public function hasPreviousExample(Attempt $attempt): bool
    {
        return (bool) $this->exampleRepository->findLastByAttempt($attempt);
    }

    public function getData(Attempt $attempt): ?array
    {
        $exampleRepository = $this->exampleRepository;

        if (!$example = $exampleRepository->findLastUnansweredByAttempt($attempt)) {
            return null;
        }

        $example->setEntityRepository($exampleRepository);
        $attempt->setEntityRepository($this);

        return [
            'ex' => [
                'num' => $example->getNumber(),
                'str' => "$example",
            ],
            'errors' => $attempt->getErrorsCount(),
            'exRem' => $attempt->getRemainedExamplesCount(),
            'limTime' => $attempt->getLimitTime()->getTimestamp(),
        ];
    }

    public function getRemainedExamplesCount(Attempt $attempt): int
    {
        $count = $attempt->getSettings()->getExamplesCount() - $this->getSolvedExamplesCount($attempt);

        return $count > 0 ? $count : 0;
    }

    public function getRemainedTime(Attempt $attempt): \DateTimeInterface
    {
        $remainedTime = $attempt->getLimitTime()->getTimestamp() - time();

        return $this->dts($remainedTime > 0 ? $remainedTime : 0);
    }

    public function getAllData(Attempt $attempt): array
    {
        $data = $attempt->setEntityRepository($this)->getData();
        $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->enableMagicCall()
            ->getPropertyAccessor();

        foreach (arr('title number finishTime solvedExamplesCount answeredExamplesCount errorsCount rating') as $property) {
            $data[$property] = $propertyAccessor->getValue($attempt, $property);
        }

        return $data;
    }

    public function getSolvedTime(Attempt $attempt): \DateTimeInterface
    {
        return $this->dts(
            $this->getFinishTime($attempt)->getTimestamp() - $attempt->getAddTime()->getTimestamp()
        );
    }

    public function getAverSolveTime(Attempt $attempt): \DateTimeInterface
    {
        $count = $this->getSolvedExamplesCount($attempt);

        return $this->dts(
            $count ? round($this->getSolvedTime($attempt)->getTimestamp() / $count) : 0
        );
    }

    public function getRongExamplesCount(Attempt $attempt): int
    {
        return $this->getErrorsCount($attempt) + $attempt->getExamplesCount() - $this->getSolvedExamplesCount($attempt);
    }

    public function findByUser(User $user): array
    {
        return $this->createQuery('select a from App:Attempt a
join a.session s
join s.user u
where u = ?1')
            ->setParameter(1, $user)
            ->getResult();
    }

    public function isDone(Attempt $attempt): bool
    {
        return $this->getSolvedExamplesCount($attempt) === $attempt->getSettings()->getExamplesCount();
    }

    public function findByUserAndTask(User $user, Task $task): array
    {
        return $this->createQuery('select a from App:Attempt a
join a.session s
where a.task = :task and s.user = :user')
            ->setParameters(['task' => $task, 'user' => $user])
            ->getResult();
    }

    public function findLastOneByTaskAndUser(Task $task, User $user): ?Attempt
    {
        return $this->createQueryBuilder('a')
            ->select('a')
            ->join('a.session', 's')
            ->where('a.task = :task')
            ->andWhere('s.user = :user')
            ->orderBy('a.addTime', 'desc')
            ->getQuery()
            ->setParameters([
                'task' => $task,
                'user' => $user,
            ])
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    public function countByUserAndTask(User $user, Task $task): int
    {
        return $this->createQueryBuilder('a')
            ->select('count(a)')
            ->join('a.session', 's')
            ->where('s.user = :user')
            ->andWhere('a.task = :task')
            ->getQuery()
            ->setParameters([
                'user' => $user,
                'task' => $task,
            ])
            ->getSingleScalarResult();
    }

    public function countDoneByCurrentUserAndTask(Task $task): int
    {
        return \count($this->findDoneByCurrentUserAndTask($task));
    }

    public function findDoneByUserAndTask(User $user, Task $task): array
    {
        $attempts = $this->findByUserAndTask($user, $task);

        return array_filter($attempts, function (Attempt $attempt): bool {
            return $this->isDone($attempt);
        });
    }

    public function getDoneAverageRatingByCurrentUserAndTask(Task $task): ?float
    {
        return $this->getDoneAverageRatingByUserAndTask($this->userLoader->getUser(), $task);
    }

    public function getDoneAverageRatingByUserAndTask(User $user, Task $task): ?float
    {
        return $this->getAverageRatingByAttempts($this->findDoneByUserAndTask($user, $task));
    }

    public function getAverageRatingByUserAndTask(User $user, Task $task): ?float
    {
        return $this->getAverageRatingByAttempts($this->findByUserAndTask($user, $task));
    }

    private function getAverageRatingByAttempts(array $attempts): ?float
    {
        $attemptsCount = \count($attempts);
        $ratingSumm = array_reduce($attempts, function (int $ratingSumm, Attempt $attempt): int {
            return $ratingSumm + $this->getRating($attempt);
        }, 0);

        return $attemptsCount ? $ratingSumm / $attemptsCount : null;
    }

    public function getAverageRatingByCurrentUserAndTask(Task $task): ?float
    {
        return $this->getAverageRatingByUserAndTask($this->userLoader->getUser(), $task);
    }

    public function findByCurrentUserAndTask(Task $task): array
    {
        return $this->findByUserAndTask($this->userLoader->getUser(), $task);
    }

    public function countByCurrentUserAndTask(Task $task): int
    {
        return \count($this->findByCurrentUserAndTask($task));
    }

    public function findDoneByCurrentUserAndTask(Task $task): array
    {
        return $this->findDoneByUserAndTask($this->userLoader->getUser(), $task);
    }

    public function findByCurrentUserAndHomework(Task $task): array
    {
        return $this->createQuery('select a from App:Attempt a
join a.session s
where s.user = :user and a.task = :task')
            ->setParameters(['user' => $this->userLoader->getUser(), 'task' => $task])
            ->getResult();
    }

    public function createAttemptResponse(Attempt $attempt): AttemptResponse
    {
        /** @var ExampleRepository $exampleRepository */
        $exampleRepository = $this->getEntityRepository(Example::class);
        $limitTime = $attempt->getLimitTime();
        $remainedExamplesCount = $this->getRemainedExamplesCount($attempt);
        $isFinished = !$this->isActual($attempt);

        if (!$isFinished) {
            $example = $exampleRepository->findLastUnansweredByAttemptOrGetNew($attempt);
            $exampleNumber = $exampleRepository->getNumber($example);
            $exampleResponse = new ExampleResponse($example, $exampleNumber);
        }

        return new AttemptResponse(
            $this->getNumber($attempt),
            $isFinished,
            !$isFinished ? $exampleResponse : null,
            $limitTime,
            $this->getErrorsCount($attempt),
            $remainedExamplesCount,
            $attempt->getSettings()
        );
    }
}
