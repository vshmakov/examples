<?php

namespace App\Response\Result;

use App\Entity\Attempt;
use App\Entity\Task;
use App\Entity\User;

final class ContractorResult
{
    /**
     * @var User
     */
    private $contractor;

    /**
     * @var Task
     */
    private $task;

    /**
     * @var Attempt|null
     */
    private $lastAttempt;

    /**
     * @var int
     */
    private $rightExamplesCount;

    /**
     * @var int|null
     */
    private $rating;
    /**
     * @var int
     */
    private $doneAttemptsCount;

    public function getContractor(): User
    {
        return $this->contractor;
    }

    public function setContractor(User $contractor): void
    {
        $this->contractor = $contractor;
    }

    public function getTask(): Task
    {
        return $this->task;
    }

    public function setTask(Task $task): void
    {
        $this->task = $task;
    }

    public function getLastAttempt(): ?Attempt
    {
        return $this->lastAttempt;
    }

    public function setLastAttempt(?Attempt $lastAttempt): void
    {
        $this->lastAttempt = $lastAttempt;
    }

    public function getRightExamplesCount(): int
    {
        return $this->rightExamplesCount;
    }

    public function setRightExamplesCount(int $rightExamplesCount): void
    {
        $this->rightExamplesCount = $rightExamplesCount;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(?int $rating): void
    {
        $this->rating = $rating;
    }

    public function getDoneAttemptsCount(): int
    {
        return $this->doneAttemptsCount;
    }

    public function setDoneAttemptsCount(int $doneAttemptsCount): void
    {
        $this->doneAttemptsCount = $doneAttemptsCount;
    }

    public function isDone(): bool
    {
        return $this->getDoneAttemptsCount() === $this->getTask()->getTimesCount();
    }
}
