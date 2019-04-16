<?php

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
}
