<?php

namespace App\Repository;

use App\Entity\Attempt;
use App\Entity\Example;
use App\Entity\Task;
use App\Entity\User;
use App\Service\ExampleManager;
use App\Service\UserLoader;
use App\Utils\Cache\GlobalCache;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ExampleRepository extends ServiceEntityRepository
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

    public function findLastUnansweredByAttempt(Attempt $attempt): ? Example
    {
        return $this->getValue(
            $this->createQuery('select e from App:Example e
where e.attempt = :a and e.answer is null
order by e.addTime desc')
                ->setParameter('a', $attempt)
        );
    }

    public function findLastByAttempt(Attempt $attempt): ? Example
    {
        return $this->getValue(
            $this->createQuery('select e from App:Example e
where e.attempt = :a 
order by e.addTime desc')
                ->setParameter('a', $attempt)
        );
    }

    public function getErrorNum(Example $example): ? int
    {
        if (false !== $example->isRight()) {
            return null;
        }

        return $this->getValue(
            $this->createQuery('select count(e) from App:Example e
where e.attempt = :a and e.isRight = false and e.addTime <= :dt')
                ->setParameters(['a' => $example->getAttempt(), 'dt' => $example->getAddTime()])
        );
    }

    public function getNumber(Example $example): int
    {
        $where = $example->getAttempt()->getSettings()->isDemanding() ? ' and e.isRight != false' : '';

        return $this->getValue(
            $this->createQuery("select count(e) from App:Example e
where e.attempt = :a and e.addTime < :dt $where")
                ->setParameters([
                    'a' => $example->getAttempt(),
                    'dt' => $example->getAddTime(),
                ])
        ) + 1;
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

    public function getSolvingTime(Example $example): \DateTimeInterface
    {
        $solvingTime = !$example->isAnswered() ? null
            : $this->globalCache->get(['examples[%s].solvingTime', $example], function () use ($example) {
                $previousExample = $this->getValue(
                $this->createQuery('select e from App:Example e
where e.attempt = :att and e.addTime < :dt
order by e.addTime desc')
                    ->setParameters([
                        'dt' => $example->getAddTime(),
                        'att' => $example->getAttempt(),
                    ])
            );
                $previousTime = $previousExample ? $previousExample->getAnswerTime() : $example->getAttempt()->getAddTime();

                return $example->getAnswerTime()->getTimestamp() - $previousTime->getTimestamp();
            });

        return $this->dts($solvingTime);
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
        return $this->getValue(
            $this->createQuery('select count(e) from App:Example e
join e.attempt a
join a.session s
join s.user u
where u = :u and e.addTime < :dt and (e.isRight != false or e.isRight is null)')
                ->setParameters([
                    'u' => $example->getAttempt()->getSession()->getUser(),
                    'dt' => $example->getAddTime(),
                ])
        ) + 1;
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
}
