<?php

namespace App\Repository;

use  App\Attempt\AttemptCreatorInterface;
use App\Attempt\AttemptProviderInterface;
use App\Attempt\AttemptResponseProviderInterface;
use App\Attempt\AttemptResultProviderInterface;
use App\Attempt\Example\ExampleResponseProviderInterface;
use App\Attempt\Settings\SettingsProviderInterface;
use App\DateTime\DateTime as DT;
use App\Doctrine\QueryResult;
use App\Entity\Attempt;
use App\Entity\Attempt\Result;
use App\Entity\Task;
use App\Entity\User;
use App\Object\ObjectAccessor;
use App\Repository\Traits\BaseTrait;
use App\Response\AttemptResponse;
use App\Security\User\CurrentUserProviderInterface;
use App\Security\User\CurrentUserSessionProviderInterface;
use App\Service\ExampleManager;
use App\Service\UserLoader;
use App\Utils\Cache\LocalCache;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class AttemptRepository extends ServiceEntityRepository implements AttemptCreatorInterface, AttemptProviderInterface, AttemptResponseProviderInterface, AttemptResultProviderInterface
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

    /** @var ExampleResponseProviderInterface */
    private $exampleResponseProvider;

    /** @var SettingsProviderInterface */
    private $settingsProvider;

    public function __construct(
        RegistryInterface $registry,
        ExampleRepository $exampleRepository,
        UserLoader $userLoader,
        UserRepository $userRepository,
        CurrentUserProviderInterface $currentUserProvider,
        CurrentUserSessionProviderInterface $currentUserSessionProvider,
        AuthorizationCheckerInterface $authorizationChecker,
        LocalCache $localCache,
        ExampleResponseProviderInterface $exampleResponseProvider,
        SettingsProviderInterface $settingsProvider
    ) {
        parent::__construct($registry, Attempt::class);

        $this->exampleRepository = $exampleRepository;
        $this->userLoader = $userLoader;
        $this->userRepository = $userRepository;
        $this->currentUserProvider = $currentUserProvider;
        $this->authorizationChecker = $authorizationChecker;
        $this->localCache = $localCache;
        $this->currentUserSessionProvider = $currentUserSessionProvider;
        $this->exampleResponseProvider = $exampleResponseProvider;
        $this->settingsProvider = $settingsProvider;
    }

    public function getLastAttempt(): ?Attempt
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

    private function findLastByCurrentUser(): ?Attempt
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
            ->andWhere('a.addTime <= :createdAt')
            ->getQuery()
            ->setParameters([
                'user' => $attempt->getSession()->getUser(),
                'createdAt' => $attempt->getAddTime(),
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
        return $this->exampleRepository->count([
            'attempt' => $attempt,
            'isRight' => false,
        ]);
    }

    private function getRating(Attempt $attempt): int
    {
        return ExampleManager::rating($attempt->getExamplesCount(), $this->getWrongExamplesCount($attempt));
    }

    public function createAttempt(): Attempt
    {
        $attempt = $this->createNewByCurrentUser();
        $attempt->setSettings($this->settingsProvider->getOrCreateSettingsByCurrentUserProfile());

        $entityManager = $this->getEntityManager();
        $entityManager->persist($attempt);
        $entityManager->flush($attempt);
        $this->updateAttemptResult($attempt);

        return $attempt;
    }

    /**
     * @deprecated
     */
    public function getNewByCurrentUserAndTask(Task $task): Attempt
    {
        $attempt = $this->createNewByCurrentUser()
            ->setTask($task)
            ->setSettings($task->getSettings());

        $entityManager = $this->getEntityManager();
        $entityManager->persist($attempt);
        $this->updateAttemptResult($attempt);
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
            return $this->isDone($attempt) ? $doneAttemptsCount + 1 : $doneAttemptsCount;
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
}
