<?php

namespace App\Repository;

use App\DataFixtures\Attempt\ProfileFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Attempt;
use App\Entity\Profile;
use App\Entity\Task;
use App\Entity\User;
use App\Service\AuthChecker;
use App\Utils\Cache\LocalCache;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

final class UserRepository extends ServiceEntityRepository
{
    use BaseTrait;

    private $authChecker;
    private $localCache;

    public function __construct(RegistryInterface $registry, AuthChecker $authChecker, LocalCache $localCache)
    {
        parent::__construct($registry, User::class);
        $this->authChecker = $authChecker;
        $this->localCache = $localCache;
    }

    public function getCurrentProfile(User $user)
    {
        $profileRepository = $this->getEntityRepository(Profile::class);
        $profile = $user->getProfile() ?? $profileRepository->findOneByAuthor($user) ?? $profileRepository->findOnePublic();
        $testProfileDescription = ProfileFixtures::GUEST_PROFILE_DESCRIPTION;

        if (!$profile) {
            $profile = $profileRepository->getNewByCurrentUser()
                ->setDescription($testProfileDescription)
                ->setIsPublic(true)
                ->setAuthor($this->getGuest());

            $entityManager = $this->getEntityManager();
            $entityManager->persist($profile);
            $entityManager->flush();
        }

        return $this->authChecker->isGranted('PRIV_APPOINT_PROFILES', $user) ? $profile
            : $profileRepository->findOneBy(['description' => $testProfileDescription, 'isPublic' => true]);
    }

    public function getAttemptsCount(User $user)
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

    public function getExamplesCount(User $user)
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

    public function getProfilesCount(User $user): int
    {
        return $this->getEntityManager()
            ->getRepository(Profile::class)
            ->countByAuthor($user);
    }

    public function getGuest()
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

    public function getNew()
    {
        return (new User())
            ->setEnabled(true);
    }

    public function findOneByUloginCredentials($credentials)
    {
        extract($credentials);

        return $this->findOneBy(['network' => $network, 'networkId' => $uid]);
    }

    public function findOneByUloginCredentialsOrNew($credentials)
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

    public function clearNotEnabledUsers(\DateTimeInterface $dt)
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

    public function getFinishedCountByTask(Task $task): int
    {
        $finishedUsersCount = 0;
        $taskRepository = $this->getEntityRepository(Task::class);

        foreach ($task->getContractors()->toArray() as $user) {
            if ($taskRepository->isDoneByUser($task, $user)) {
                ++$finishedUsersCount;
            }
        }

        return $finishedUsersCount;
    }
}
