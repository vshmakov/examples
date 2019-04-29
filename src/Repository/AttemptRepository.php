<?php

namespace App\Repository;

use  App\Attempt\AttemptFactoryInterface;
use App\Attempt\AttemptProviderInterface;
use App\Attempt\AttemptResponseFactoryInterface;
use App\Attempt\AttemptResultProviderInterface;
use App\Attempt\Example\ExampleResponseFactoryInterface;
use App\Attempt\RatingGeneratorInterface;
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
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

final class AttemptRepository extends ServiceEntityRepository implements AttemptFactoryInterface, AttemptProviderInterface, AttemptResponseFactoryInterface, AttemptResultProviderInterface
{
    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;

    /** @var CurrentUserSessionProviderInterface */
    private $currentUserSessionProvider;

    /** @var ExampleResponseFactoryInterface */
    private $exampleResponseProvider;

    /** @var SettingsProviderInterface */
    private $settingsProvider;

    /** @var RatingGeneratorInterface */
    private $ratingGenerator;

    public function __construct(
        RegistryInterface $registry,
        CurrentUserProviderInterface $currentUserProvider,
        CurrentUserSessionProviderInterface $currentUserSessionProvider,
        ExampleResponseFactoryInterface $exampleResponseProvider,
        SettingsProviderInterface $settingsProvider,
        RatingGeneratorInterface $ratingGenerator
    ) {
        parent::__construct($registry, Attempt::class);

        $this->currentUserProvider = $currentUserProvider;
        $this->currentUserSessionProvider = $currentUserSessionProvider;
        $this->exampleResponseProvider = $exampleResponseProvider;
        $this->settingsProvider = $settingsProvider;
        $this->ratingGenerator = $ratingGenerator;
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
        return (int) $this->createQueryBuilder('a')
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

        return (int) $this->createQueryBuilder('a')
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
        return (int) $this->createQueryBuilder('a')
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

    /**
     * @deprecated
     */
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
        $result = $attempt->getResult();
        $result->setRating(
            $this->ratingGenerator->generateRating($result->getErrorsCount() + $result->getRemainedExamplesCount(), $attempt->getSettings()->getExamplesCount())
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
