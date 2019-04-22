<?php

declare(strict_types=1);

namespace App\Response\Result;

use App\Entity\Task;

final class TaskResult
{
    /**
     * @var Task
     */
    private $task;

    /**
     * @var int
     */
    private $doneContractorsCount;

    /**
     * @var int
     */
    private $donePercent;

    public function getTask(): Task
    {
        return $this->task;
    }

    public function setTask(Task $task): void
    {
        $this->task = $task;
    }

    public function getDoneContractorsCount(): int
    {
        return $this->doneContractorsCount;
    }

    public function setDoneContractorsCount(int $doneContractorsCount): void
    {
        $this->doneContractorsCount = $doneContractorsCount;
    }

    public function getDonePercent(): int
    {
        return $this->donePercent;
    }

    public function setDonePercent(int $donePercent): void
    {
        $this->donePercent = $donePercent;
    }
}
