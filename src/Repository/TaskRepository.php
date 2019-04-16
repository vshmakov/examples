<?php

namespace App\Repository;

use App\Attempt\AttemptProviderInterface;
use App\Attempt\Example\ExampleProviderInterface;
use App\Entity\Task;
use App\Entity\User;
use App\Object\ObjectAccessor;
use App\Response\Result\TaskResult;
use App\Security\User\CurrentUserProviderInterface;
use App\Task\Contractor\ContractorProviderInterface;
use App\Task\TaskProviderInterface;
use App\Task\TaskResultFactoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Webmozart\Assert\Assert;

final class TaskRepository extends ServiceEntityRepository implements TaskProviderInterface, TaskResultFactoryInterface
{
    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;

    /** @var ContractorProviderInterface */
    private $contractorProvider;

    /** @var ExampleProviderInterface */
    private $exampleProvider;

    public function __construct(
        RegistryInterface $registry,
        CurrentUserProviderInterface $currentUserProvider,
        AttemptProviderInterface $attemptProvider,
        ContractorProviderInterface $contractorProvider,
        ExampleProviderInterface $exampleProvider
    ) {
        parent::__construct($registry, Task::class);

        $this->currentUserProvider = $currentUserProvider;
        $this->attemptProvider = $attemptProvider;
        $this->contractorProvider = $contractorProvider;
        $this->exampleProvider = $exampleProvider;
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

    public function createTaskResult(Task $task): TaskResult
    {
        return ObjectAccessor::initialize(TaskResult::class, [
            'task' => $task,
            'doneContractorsCount' => $this->contractorProvider->getSolvedContractorsCount($task),
            'donePercent' => $this->getDonePercent($task),
        ]);
    }

    private function getDonePercent(Task $task): int
    {
        $contractorsCount = $task->getContractors()->count();

        return round(
            array_reduce($task->getContractors()->toArray(), function (int $donePercent, User $contractor) use ($task, $contractorsCount): float {
                if (0 === $contractorsCount) {
                    return 0;
                }

                return $donePercent + $this->exampleProvider->getRightExamplesCount($contractor, $task) / $task->getTotalExamplesCount() / $contractorsCount * 100;
            }, 0)
        );
    }
}
