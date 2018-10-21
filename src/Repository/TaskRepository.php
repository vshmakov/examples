<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\Attempt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Entity\User;

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
        
        foreach ($task->getContractors()->toArray() as $user) {
            if ($this->isSolvedByUser($task, $user)) {
                ++$finishedUsersCount;
            }
        }

        return $finishedUsersCount;
    }

    public function isSolvedByUser(Task $task, User $user) : bool
    {
        $attemptRepository = $this->getEntityRepository(Attempt::class);
        $userAttempts = $attemptRepository->findByTaskAndUser($task, $user);
        $outstandingAttemptsCount = array_reduce($userAttempts, function (int $outstandingAttemptsCount, Attempt $attempt) use ($attemptRepository) : int {
            if ($attemptRepository->isDone($attempt)) {
                --$outstandingAttemptsCount;
            }

            return $outstandingAttemptsCount;
        }, $task->getTimesCount());

        return $outstandingAttemptsCount == 0;
    }
}
