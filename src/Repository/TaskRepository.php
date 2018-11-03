<?php

namespace App\Repository;

use App\Entity\Attempt;
use App\Entity\Task;
use App\Entity\User;
use App\Service\AuthChecker;
use App\Service\UserLoader;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TaskRepository extends ServiceEntityRepository
{
    use BaseTrait;
    private $authChecker;
    private $userLoader;

    public function __construct(RegistryInterface $registry, UserLoader $userLoader, AuthChecker $authChecker)
    {
        parent::__construct($registry, Task::class);
        $this->userLoader = $userLoader;
        $this->authChecker = $authChecker;
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

        return 0 === $outstandingAttemptsCount;
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

    public function findByCurrentAuthor() : array
    {
        return $this->findByAuthor($this->userLoader->getUser());
    }

    public function isActual(Task $task) : bool
    {
        return time() > $task->getAddTime()->getTimestamp()
            && time() < $task->getLimitTime()->getTimestamp()
            && $this->getEntityRepository(User::class)->getFinishedCountByTask($task) < $task->getContractors()->count();
    }

    public function countActualHomeworksByCurrentUser() : int
    {
        return \count(array_filter($this->findHomeworkByCurrentUser(), function (Task $homework) : bool {
            return $this->authChecker->isGranted('SOLVE', $homework);
        }));
    }

    public function findActualByAuthor(User $author) : array
    {
        return array_filter($this->findByAuthor($author), function (Task $task) : bool {
            return $this->isActual($task);
        });
    }
}
