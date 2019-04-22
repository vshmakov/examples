<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use App\Security\User\CurrentUserProviderInterface;
use App\Task\Contractor\ContractorProviderInterface;
use App\Task\Homework\HomeworkProviderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Webmozart\Assert\Assert;

final class HomeworkRepository extends ServiceEntityRepository implements HomeworkProviderInterface
{
    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;

    /** @var ContractorProviderInterface */
    private $contractorProvider;

    public function __construct(RegistryInterface $registry, CurrentUserProviderInterface $currentUserProvider, ContractorProviderInterface $contractorProvider)
    {
        parent::__construct($registry, Task::class);

        $this->currentUserProvider = $currentUserProvider;
        $this->contractorProvider = $contractorProvider;
    }

    public function getActualHomework(): array
    {
        return array_filter($this->getAllHomework(), function (Task $task): bool {
            return !$this->contractorProvider->isDoneByCurrentContractor($task);
        });
    }

    public function getArchiveHomework(): array
    {
        return array_filter($this->getAllHomework(), function (Task $task): bool {
            return $this->contractorProvider->isDoneByCurrentContractor($task);
        });
    }

    /**
     * @throws \InvalidArgumentException if current user has no teacher
     */
    private function getAllHomework(): array
    {
        $currentUser = $this->currentUserProvider->getCurrentUserOrGuest();
        Assert::true($currentUser->hasTeacher());

        return $this->findByAuthor($currentUser->getTeacher());
    }
}
