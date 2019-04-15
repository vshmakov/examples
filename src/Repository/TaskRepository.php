<?php

namespace App\Repository;

use App\Attempt\AttemptProviderInterface;
use App\Entity\Task;
use App\Security\User\CurrentUserProviderInterface;
use App\Task\Contractor\ContractorProviderInterface;
use App\Task\TaskProviderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Webmozart\Assert\Assert;

final class TaskRepository extends ServiceEntityRepository implements TaskProviderInterface
{
    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;

    /** @var ContractorProviderInterface */
    private $contractorProvider;

    public function __construct(RegistryInterface $registry, CurrentUserProviderInterface $currentUserProvider, AttemptProviderInterface $attemptProvider, ContractorProviderInterface $contractorProvider)
    {
        parent::__construct($registry, Task::class);

        $this->currentUserProvider = $currentUserProvider;
        $this->attemptProvider = $attemptProvider;
        $this->contractorProvider = $contractorProvider;
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
            && $this->contractorProvider->getSolvedContractorsCount($task) < $task->getContractors()->count();
    }
}
