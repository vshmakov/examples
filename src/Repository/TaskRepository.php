<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\Attempt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Entity\User;
use App\Service\UserLoader;

class TaskRepository extends ServiceEntityRepository
{
    use BaseTrait;
    private $userLoader;

    public function __construct(RegistryInterface $registry, UserLoader $userLoader)
    {
        parent::__construct($registry, Task::class);
        $this->userLoader = $userLoader;
    }

    public function isDoneByUser(Task $task, User $user) : bool
    {
        $attemptRepository = $this->getEntityRepository(Attempt::class);
        $userAttempts = $attemptRepository->findByUserAndTask($user, $task);
        $outstandingAttemptsCount = array_reduce($userAttempts, function (int $outstandingAttemptsCount, Attempt $attempt) use ($attemptRepository) : int {
            if ($attemptRepository->isDone($attempt)) {
                --$outstandingAttemptsCount;
            }

            return $outstandingAttemptsCount;
        }, $task->getTimesCount());

        return $outstandingAttemptsCount == 0;
    }

    public function findHomeworkByCurrentUser() : array
    {
        return $this->createQuery('select t from App:Task t
        join t.contractors c
        where c = :user')
            ->setParameters(['user' => $this->userLoader->getUser()])
            ->getResult();
    }

    public function isDoneByCurrentUser(Task $task) : bool
    {
        return $this->isDoneByUser($task, $this->userLoader->getUser());
    }

    public function getFinishedUsersCount(Task $task) : int
    {
        return $this->getEntityRepository(User::class)
            ->getFinishedCountByTask($task);
    }
}
