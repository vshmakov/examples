<?php

namespace App\Repository;

use App\Attempt\AttemptProviderInterface;
use App\Entity\Task;
use App\Entity\User;
use App\Security\User\CurrentUserProviderInterface;
use App\Task\TaskProviderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Webmozart\Assert\Assert;

final class TaskRepository extends ServiceEntityRepository implements TaskProviderInterface
{
    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var AttemptProviderInterface */
    private $attemptProvider;

    public function __construct(RegistryInterface $registry, CurrentUserProviderInterface $currentUserProvider, AuthorizationCheckerInterface $authorizationChecker, AttemptProviderInterface $attemptProvider)
    {
        parent::__construct($registry, Task::class);

        $this->currentUserProvider = $currentUserProvider;
        $this->authorizationChecker = $authorizationChecker;
        $this->attemptProvider = $attemptProvider;
    }

    public function getActualTasksOfCurrentUser(): array
    {
        return array_filter($this->findByCurrentAuthor(), [$this, 'isActual']);
    }

    public function getArchiveTasksOfCurrentUser(): array
    {
        return array_filter($this->findByCurrentAuthor(), function (Task $task): bool {
            return !$this->isActual($task);
        });
    }

    private function findByCurrentAuthor(): array
    {
        $currentUser = $this->currentUserProvider->getCurrentUserOrGuest();
        Assert::true($currentUser->isTeacher(), 'There is impossible to get task of not teacher user');

        return $this->findByAuthor($currentUser);
    }

    private function isActual(Task $task): bool
    {
        return time() > $task->getAddTime()->getTimestamp()
            && time() < $task->getLimitTime()->getTimestamp()
            && $this->getSolvedUsersCount($task) < $task->getContractors()->count();
    }

    private function getSolvedUsersCount(Task $task): int
    {
        $solvedUsersCount = 0;

        foreach ($task->getContractors()->toArray() as $user) {
            if ($this->isDoneByUser($task, $user)) {
                ++$solvedUsersCount;
            }
        }

        return $solvedUsersCount;
    }

    private function isDoneByUser(Task $task, User $user): bool
    {
        return $task->getTimesCount() === $this->attemptProvider->getDoneAttemptsCount($task, $user);
    }
}
