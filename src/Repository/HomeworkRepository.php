<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use App\Security\User\CurrentUserProviderInterface;
use App\Task\Homework\HomeworkProviderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Webmozart\Assert\Assert;

final class HomeworkRepository extends ServiceEntityRepository implements HomeworkProviderInterface
{
    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(RegistryInterface $registry, CurrentUserProviderInterface $currentUserProvider, AuthorizationCheckerInterface $authorizationChecker)
    {
        parent::__construct($registry, Task::class);

        $this->currentUserProvider = $currentUserProvider;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function findHomeworkByCurrentUser(): array
    {
        return $this->getEntityManager()
            ->createQuery('select t from App:Task t
        join t.contractors c
        where c = :user')
            ->setParameters(['user' => $this->currentUserProvider->getCurrentUserOrGuest()])
            ->getResult();
    }

    public function isDoneByCurrentUser(Task $task): bool
    {
        return $this->isDoneByUser($task, $this->currentUserProvider->getCurrentUserOrGuest());
    }

    public function getFinishedUsersCount(Task $task): int
    {
        return $this->getEntityRepository(User::class)
            ->getFinishedCountByTask($task);
    }

    public function countActualHomeworksByCurrentUser(): int
    {
        return \count(array_filter($this->findHomeworkByCurrentUser(), function (Task $homework): bool {
            return $this->authorizationChecker->isGranted('SOLVE', $homework);
        }));
    }

    /**
     * @throws \InvalidArgumentException if current user has no teacher
     */
    public function getActualHomeworkOfCurrentUserTeacher(): array
    {
        $currentUser = $this->currentUserProvider->getCurrentUserOrGuest();
        Assert::true($currentUser->hasTeacher());

        return array_filter($this->findByAuthor($currentUser->getTeacher()), function (Task $task): bool {
            return $this->isActual($task);
        });
    }
}
