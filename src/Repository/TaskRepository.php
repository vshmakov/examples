<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\Attempt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TaskRepository extends ServiceEntityRepository
{
    use BaseTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function getFinishedUsersCount(Task $task) : int
    {
        $finishedUsersCount = 0;
        $attemptRepository = $this->getEntityRepository(Attempt::class);

        foreach ($task->getContractors()->toArray() as $user) {
            $userAttempts = $this->createQuery('select a from App:Attempt a
            join a.session s
where a.task = :task and s.user = :user')
                ->setParameters(['task' => $task, 'user' => $user])
                ->getResult();

            $outstandingAttemptsCount = array_reduce($userAttempts, function (int $outstandingAttemptsCount, Attempt $attempt) use ($attemptRepository) : int {
                if ($attemptRepository->isDone($attempt)) {
                    --$outstandingAttemptsCount;
                }

                return $outstandingAttemptsCount;
            }, $task->getTimesCount());

            if ($outstandingAttemptsCount == 0) {
                ++$finishedUsersCount;
            }
        }

        return $finishedUsersCount;
    }
}
