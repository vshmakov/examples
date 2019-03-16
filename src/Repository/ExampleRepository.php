<?php

namespace App\Repository;

use  App\Attempt\Example\ExampleResponseProviderInterface;
use App\DateTime\DateTime as DT;
use App\Entity\Attempt;
use App\Entity\Example;
use App\Entity\Task;
use App\Entity\User;
use App\Response\ExampleResponse;
use App\Service\ExampleManager;
use App\Service\UserLoader;
use App\Utils\Cache\GlobalCache;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ExampleRepository extends ServiceEntityRepository implements ExampleResponseProviderInterface
{
    use BaseTrait;
    private $exampleManager;
    private $userLoader;
    private $globalCache;

    public function __construct(RegistryInterface $registry, ExampleManager $exampleManager, UserLoader $userLoader, GlobalCache $globalCache)
    {
        parent::__construct($registry, Example::class);
        $this->exampleManager = $exampleManager;
        $this->userLoader = $userLoader;
        $this->globalCache = $globalCache;
    }

    public function findLastUnansweredByAttempt(Attempt $attempt): ?Example
    {
        return $this->createQueryBuilder('e')
            ->select('e')
            ->where('e.attempt = :attempt')
            ->andWhere('e.answer is null')
            ->orderBy('e.addTime', 'desc')
            ->getQuery()
            ->setParameter('attempt', $attempt)
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    public function findLastByAttempt(Attempt $attempt): ?Example
    {
        return $this->createQueryBuilder('e')
            ->select('e')
            ->where('e.attempt = :attempt')
            ->orderBy('e.addTime', 'desc')
            ->getQuery()
            ->setParameter('attempt', $attempt)
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    private function getErrorNumber(Example $example): ?int
    {
        if (false !== $example->isRight()) {
            return null;
        }

        return $this->createQueryBuilder('e')
            ->select('count(e)')
            ->where('e.attempt = :attempt')
            ->andWhere('e.isRight = false')
            ->andWhere('e.addTime <= :createdAt')
            ->getQuery()
            ->setParameters([
                'attempt' => $example->getAttempt(),
                'createdAt' => $example->getAddTime(),
            ])
            ->getSingleScalarResult();
    }

    public function getNumber(Example $example): int
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->select('count(e)')
            ->where('e.attempt = :attempt')
            ->andWhere('e.addTime < :createdAt');

        if ($example->getAttempt()->getSettings()->isDemanding()) {
            $queryBuilder->andWhere('e.isRight != false');
        }

        return $queryBuilder
                ->getQuery()
                ->setParameters([
                    'attempt' => $example->getAttempt(),
                    'createdAt' => $example->getAddTime(),
                ])
                ->getSingleScalarResult()
            + 1;
    }

    public function findLastUnansweredByAttemptOrGetNew(Attempt $attempt): Example
    {
        return $this->findLastUnansweredByAttempt($attempt) ?? $this->getNew($attempt);
    }

    public function getNew(Attempt $attempt): Example
    {
        $example = (new Example())
            ->setAttempt($attempt);

        $lastExample = $this->findLastByAttempt($attempt);

        if ($attempt->getSettings()->isDemanding() && $lastExample && !$lastExample->isRight()) {
            $example->setFirst($lastExample->getFirst())
                ->setSecond($lastExample->getSecond())
                ->setSign($lastExample->getSign());
        } else {
            ($settings = $attempt->getSettings()->getSettings());
            $exampleManager = $this->exampleManager;
            $sign = $exampleManager->getRandomSign($settings);
            $previousExamples = $this->createQuery('select e from App:Example e
join e.attempt a
join a.session s
join s.user u
where u = :u and a.addTime > :dt')
                ->setParameters([
                    'u' => $this->userLoader->getUser(),
                    'dt' => (new \DateTime())->sub(new \DateInterval('P3D')),
                ])
                ->getResult();

            $exampleData = (object) $exampleManager->getRandomExample($sign, $settings, $previousExamples);
            $example->setFirst($exampleData->first)
                ->setSecond($exampleData->second)
                ->setSign($exampleData->sign);
        }

        $entityManager = $this->getEntityManager();
        $entityManager->persist($example);
        $entityManager->flush();

        return $example;
    }

    public function getSolvingTime(Example $example): ?\DateTimeInterface
    {
        $solvingTime = !$example->isAnswered() ? null
            : $this->globalCache->get(['examples[%s].solvingTime', $example], function () use ($example) {
                $previousExample = $this->createQueryBuilder('e')
                    ->select('e')
                    ->where('e.attempt = :attempt')
                    ->andWhere('e.addTime < :createdAt')
                    ->orderBy('e.addTime', 'desc')
                    ->getQuery()
                    ->setParameters([
                        'attempt' => $example->getAttempt(),
                        'createdAt' => $example->getAddTime(),
                    ])
                    ->setMaxResults(1)
                    ->getOneOrNullResult();
                $previousTime = $previousExample ? $previousExample->getAnswerTime() : $example->getAttempt()->getAddTime();

                return null !== $example->getAnswerTime() ? $example->getAnswerTime()->getTimestamp() - $previousTime->getTimestamp() : null;
            });

        return null !== $solvingTime ? DT::createFromTimestamp($solvingTime) : null;
    }

    public function findByUser(User $user): array
    {
        return $this->createQuery('select e from App:Example e
join e.attempt a
join a.session s
where s.user = :u')
            ->setParameter('u', $user)
            ->getResult();
    }

    public function getUserNumber(Example $example): int
    {
        return $this->createQueryBuilder('e')
                ->select('count(e)')
                ->join('e.attempt', 'a')
                ->join('a.session', 's')
                ->where('s.user = :user')
                ->andWhere('e.addTime < :createdAt')
                ->andWhere('(e.isRight != false or e.isRight is null)')
                ->getQuery()
                ->setParameters([
                    'user' => $example->getAttempt()->getSession()->getUser(),
                    'createdAt' => $example->getAddTime(),
                ])
                ->getSingleScalarResult()
            + 1;
    }

    public function findByCurrentUserAndHomework(Task $task): array
    {
        return $this->createQuery('select e from App:Example e
join e.attempt a
join a.session s
where s.user = :user and a.task = :task')
            ->setParameters(['user' => $this->userLoader->getUser(), 'task' => $task])
            ->getResult();
    }

    public function findByUserAndTask(User $user, Task $task): array
    {
        return $this->createQuery('select e from App:Example e
join e.attempt a
join a.session s
where s.user = :user and a.task = :task')
            ->setParameters(['user' => $user, 'task' => $task])
            ->getResult();
    }

    public function createExampleResponse(Example $example): ExampleResponse
    {
        return new ExampleResponse(
            $this->getNumber($example),
            $this->getSolvingTime($example),
            $this->getErrorNumber($example),
            $example
        );
    }

    public function createSolvingExampleResponse(Attempt $attempt): ?ExampleResponse
    {
        if (($attempt->getResult()->isFinished())) {
            return null;
        }

        return $this->createExampleResponse(
            $this->findLastUnansweredByAttemptOrGetNew($attempt)
        );
    }
}
