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
        AuthorizationCheckerInterface $authorizationChecker,
        LocalCache $localCache,
        CurrentUserSessionProviderInterface $currentUserSessionProvider
    ) {
        parent::__construct($registry, Attempt::class);

        $this->exampleRepository = $exampleRepository;
        $this->userLoader = $userLoader;
        $this->userRepository = $userRepository;
        $this->authorizationChecker = $authorizationChecker;
        $this->localCache = $localCache;
        $this->currentUserSessionProvider = $currentUserSessionProvider;
    }

    public function findLastActualByCurrentUser(): ?Attempt
    {
        $attempt = $this->findLastByCurrentUser();

        /* @deprecated */
        return $this->authorizationChecker->isGranted('SOLVE', $attempt) ? $attempt : null;
    }

    public function findLastByCurrentUser(): ?Attempt
    {
        $userLoader = $this->userLoader;
        $where = !$userLoader->isCurrentUserGuest() ? 's.user = :u' : 'a.session = :s';
        $query = $this->createQuery("select a from App:Attempt a
join a.session s
where $where
order by a.addTime desc");
        $parameters = !$userLoader->isCurrentUserGuest() ? ['u' => $userLoader->getUser()] : ['s' => $this->currentUserSessionProvider->getCurrentUserSessionOrNew()];
        $query->setParameters($parameters);

        return $this->getValue($query);
    }

    public function getTitle(Attempt $attempt): string
    {
        return 'Попытка №'.$this->getNumber($attempt);
    }

    public function getNumber(Attempt $attempt): int
    {
        return $this->getValue(
            $this->createQuery('select count(a) from App:Attempt a
join a.session s
where s.user = :u and a.addTime <= :dt
')->setParameters(['u' => $attempt->GetSession()->GetUser(), 'dt' => $attempt->getAddTime()])
        );
    }

    public function getFinishTime(Attempt $attempt): \DateTimeInterface
    {
        return $this->dt($this->getValue(
            $this->createQuery('select e.answerTime from App:Attempt a
join a.examples e
where a = :att and e.answerTime is not null
order by e.answerTime desc
')->setParameter('att', $attempt)
        )) ?: $attempt->getAddTime();
    }

    public function getSolvedExamplesCount(Attempt $attempt): int
    {
        return $attempt->getSettings()->isDemanding() ? $this->getValue(
            $this->createQuery('select count(e) from App:Attempt a
join a.examples e
where e.isRight = true and a = :a
')->setParameters(['a' => $attempt])
        ) : $this->getAnsweredExamplesCount($attempt);
    }

    public function getAnsweredExamplesCount(Attempt $attempt): int
    {
        return $this->getValue(
            $this->createQuery('select count(e) from App:Attempt a
join a.examples e
where e.answer is not null and a = :a
')->setParameters(['a' => $attempt])
        );
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
        return $this->getValue(
            $this->createQuery('select count(a) from App:Attempt a
join a.session s
join s.user u
where u = :u')
                ->setParameter('u', $this->userLoader->getUser())
        );
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
        return $this->getValue(
            $this->createQuery('select a from App:Attempt a
        join a.session s
        where a.task = :task and s.user = :user
        order by a.addTime desc')
                ->setParameters(['task' => $task, 'user' => $user])
        );
    }

    public function countByUserAndTask(User $user, Task $task): int
    {
        return $this->getValue(
            $this->createQuery('select count(a) from App:Attempt a
join a.session s
where s.user = :user and a.task = :task
')
                ->setParameters(['user' => $user, 'task' => $task])
        );
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
        $isFinished = 0 === $remainedExamplesCount or time() > $limitTime->getTimestamp();

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
            $remainedExamplesCount
        );
    }
}
