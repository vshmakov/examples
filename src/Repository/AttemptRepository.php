<?php

namespace App\Repository;

use  App\Attempt\AttemptFactoryInterface;
use App\Attempt\AttemptProviderInterface;
use App\Attempt\AttemptResponseFactoryInterface;
use App\Attempt\AttemptResultProviderInterface;
use App\Attempt\Example\ExampleResponseFactoryInterface;
use App\Attempt\Settings\SettingsProviderInterface;
use App\DateTime\DateTime as DT;
use App\Doctrine\QueryResult;
use App\Entity\Attempt;
use App\Entity\Attempt\Result;
use App\Entity\Example;
use App\Entity\Task;
use App\Entity\User;
use App\Object\ObjectAccessor;
use App\Response\AttemptResponse;
use App\Security\User\CurrentUserProviderInterface;
use App\Security\User\CurrentUserSessionProviderInterface;
use App\Service\ExampleManager;
use App\Service\UserLoader;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

final class AttemptRepository extends ServiceEntityRepository implements AttemptFactoryInterface, AttemptProviderInterface, AttemptResponseFactoryInterface, AttemptResultProviderInterface
{
    private $userLoader;

    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;

    /** @var CurrentUserSessionProviderInterface */
    private $currentUserSessionProvider;

    /** @var ExampleResponseFactoryInterface */
    private $exampleResponseProvider;

    /** @var SettingsProviderInterface */
    private $settingsProvider;

    public function __construct(
        RegistryInterface $registry,
        UserLoader $userLoader,
        CurrentUserProviderInterface $currentUserProvider,
        CurrentUserSessionProviderInterface $currentUserSessionProvider,
        ExampleResponseFactoryInterface $exampleResponseProvider,
        SettingsProviderInterface $settingsProvider
    ) {
        parent::__construct($registry, Attempt::class);

        $this->userLoader = $userLoader;
        $this->currentUserProvider = $currentUserProvider;
        $this->currentUserSessionProvider = $currentUserSessionProvider;
        $this->exampleResponseProvider = $exampleResponseProvider;
        $this->settingsProvider = $settingsProvider;
    }

    public function getLastAttempt(): ?Attempt
    {
        $attempt = $this->findLastByCurrentUser();

        return null !== $attempt && null !== $attempt->getResult() && !$attempt->getResult()->isFinished() ? $attempt : null;
    }

    private function findLastByCurrentUser(): ?Attempt
    {
        return $this->createGetLastAttemptQueryBuilder($this->currentUserProvider->getCurrentUserOrGuest())
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function createGetLastAttemptQueryBuilder(User $user): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->select('a');

        if (!$this->currentUserProvider->isGuest($user)) {
            $queryBuilder
                ->join('a.session', 's')
                ->where('s.user = :user');
            $parameters = ['user' => $user];
        } else {
            $queryBuilder->where('a.session = :session');
            $parameters = ['session' => $this->currentUserSessionProvider->getCurrentUserSessionOrNew()];
        }

        return $queryBuilder
            ->orderBy('a.addTime', 'desc')
            ->setParameters($parameters)
            ->setMaxResults(1);
    }

    private function getTitle(Attempt $attempt): string
    {
        return 'Попытка №'.$this->getNumber($attempt);
    }

    private function getNumber(Attempt $attempt): int
    {
        return $this->createQueryBuilder('a')
            ->select('count(a)')
            ->join('a.session', 's')
            ->where('s.user = :user')
            ->andWhere('a.id <= :attemptId')
            ->getQuery()
            ->setParameters([
                'user' => $attempt->getSession()->getUser(),
                'attemptId' => $attempt->getId(),
            ])
            ->getSingleScalarResult();
    }

    private function getFinishTime(Attempt $attempt): \DateTimeInterface
    {
        $finishTime = QueryResult::value(
            $this->createQueryBuilder('a')
                ->select('e.answerTime')
                ->join('a.examples', 'e')
                ->where('a = :attempt')
                ->andWhere('e.answerTime is not null')
                ->orderBy('e.answerTime', 'desc')
                ->getQuery()
                ->setParameter('attempt', $attempt)
        );

        return null !== $finishTime ? DT::createFromDT($finishTime) : $attempt->getCreatedAt();
    }

    private function getSolvedExamplesCount(Attempt $attempt): int
    {
        if (!$attempt->getSettings()->isDemanding()) {
            return $this->getAnsweredExamplesCount($attempt);
        }

        return $this->createQueryBuilder('a')
            ->select('count(a)')
            ->join('a.examples', 'e')
            ->where('e.isRight = true')
            ->andWhere('a = :attempt')
            ->getQuery()
            ->setParameter('attempt', $attempt)
            ->getSingleScalarResult();
    }

    private function getAnsweredExamplesCount(Attempt $attempt): int
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

    private function getErrorsCount(Attempt $attempt): int
    {
        return $this->getEntityManager()
            ->getRepository(Example::class)
            ->count([
                'attempt' => $attempt,
                'isRight' => false,
            ]);
    }

    private function getRating(Attempt $attempt): int
    {
        return ExampleManager::rating($attempt->getExamplesCount(), $this->getWrongExamplesCount($attempt));
    }

    public function createCurrentUserAttempt(): Attempt
    {
        $attempt = $this->createNewByCurrentUser();
        $attempt->setSettings($this->settingsProvider->getOrCreateSettingsByCurrentUserProfile());

        $entityManager = $this->getEntityManager();
        $entityManager->persist($attempt);
        $entityManager->flush($attempt);
        $this->updateAttemptResult($attempt);

        return $attempt;
    }

    public function createCurrentUserSolvesTaskAttempt(Task $task): Attempt
    {
        return $this->createUserSolvesTaskAttempt($task, $this->currentUserProvider->getCurrentUserOrGuest());
    }

    public function createUserSolvesTaskAttempt(Task $task, User $user): Attempt
    {
        $attempt = $this->createNewByUser($user);
        ObjectAccessor::setValues($attempt, [
            'task' => $task,
        ]);

        $entityManager = $this->getEntityManager();
        $entityManager->persist($attempt);
        $entityManager->flush($attempt);
        $this->updateAttemptResult($attempt);

        return $attempt;
    }

    private function createNewByCurrentUser(): Attempt
    {
        return $this->createNewByUser($this->currentUserProvider->getCurrentUserOrGuest());
    }

    private function createNewByUser(User $user): Attempt
    {
        $attempt = ObjectAccessor::initialize(Attempt::class, [
            'session' => $this->currentUserSessionProvider->getUserSessionOrNew($user),
        ]);

        return $attempt;
    }

    public function getRemainedExamplesCount(Attempt $attempt): int
    {
        $count = $attempt->getSettings()->getExamplesCount() - $this->getSolvedExamplesCount($attempt);

        return $count > 0 ? $count : 0;
    }

    public function getRemainedTime(Attempt $attempt): \DateTimeInterface
    {
        $remainedTime = $attempt->getLimitTime()->getTimestamp() - time();

        return DT::createFromTimestamp($remainedTime > 0 ? $remainedTime : 0);
    }

    public function getSolvedTime(Attempt $attempt): \DateTimeInterface
    {
        $finishedTime = $this->getFinishTime($attempt);

        return DT::createFromTimestamp(
            null !== $finishedTime ? $finishedTime->getTimestamp() - $attempt->getAddTime()->getTimestamp() : 0
        );
    }

    private function getWrongExamplesCount(Attempt $attempt): int
    {
        return $this->getErrorsCount($attempt) + $attempt->getExamplesCount() - $this->getSolvedExamplesCount($attempt);
    }

    /**
     * @deprecated
     */
    public function findByUser(User $user): array
    {
        return $this->createQuery('select a from App:Attempt a
join a.session s
join s.user u
where u = ?1')
            ->setParameter(1, $user)
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

    public function getDoneAttemptsCount(Task $task, User $user): int
    {
        $attempts = $this->createQueryBuilder('a')
            ->join('a.session', 's')
            ->where('a.task = :task')
            ->andWhere('s.user = :user')
            ->getQuery()
            ->setParameters([
                'task' => $task,
                'user' => $user,
            ])
            ->getResult();

        return array_reduce($attempts, function (int $doneAttemptsCount, Attempt $attempt): int {
            return $attempt->isDone() ? $doneAttemptsCount + 1 : $doneAttemptsCount;
        }, 0);
    }

    private function isDone(Attempt $attempt): bool
    {
        return 0 === $attempt->getResult()->getRemainedExamplesCount();
    }

    public function createAttemptResponse(Attempt $attempt): AttemptResponse
    {
        return new AttemptResponse(
            $this->getNumber($attempt),
            $this->getTitle($attempt),
            $this->exampleResponseProvider->createSolvingExampleResponse($attempt),
            $attempt
        );
    }

    public function updateAttemptResult(Attempt $attempt): void
    {
        $result = $attempt->getResult() ?? new Result();
        $solvedExamplesCount = $this->getSolvedExamplesCount($attempt);
        $errorsCount = $this->getErrorsCount($attempt);
        ObjectAccessor::setValues($result, [
            'solvedExamplesCount' => $solvedExamplesCount,
            'errorsCount' => $errorsCount,
            'finishedAt' => $this->getFinishTime($attempt),
        ]);
        $attempt->setResult($result);
        $this->setRating($attempt);

        $entityManager = $this->getEntityManager();
        $entityManager->persist($result);
        $entityManager->flush($attempt);
        $entityManager->flush($result);
    }

    private function setRating(Attempt $attempt): void
    {
        $result = $attempt
            ->getResult();
        $result->setRating(
            ExampleManager::rating($result->getSolvedExamplesCount(), $result->getErrorsCount() + $result->getRemainedExamplesCount())
        );
    }

    public function getContractorLastAttempt(User $contractor, Task $task): ?Attempt
    {
        return $this->createGetLastAttemptQueryBuilder($contractor)
            ->andWhere('a.task = :task')
            ->getQuery()
            ->setParameter('task', $task)
            ->getOneOrNullResult();
    }

    public function getContractorDoneAttempts(User $contractor, Task $task): array
    {
        $attempts = $this->createQueryBuilder('a')
            ->join('a.session', 's')
            ->where('s.user = :user')
            ->andWhere('a.task = :task')
            ->getQuery()
            ->setParameters([
                'user' => $contractor,
                'task' => $task,
            ])
            ->getResult();

        return array_filter($attempts, function (Attempt $attempt): bool {
            return $attempt->isDone();
        });
    }
}
