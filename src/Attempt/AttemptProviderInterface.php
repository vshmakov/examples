<?php

declare(strict_types=1);

namespace App\Attempt;

use App\Entity\Attempt;
use App\Entity\Task;
use App\Entity\User;

interface AttemptProviderInterface
{
    public function getLastAttempt(): ?Attempt;

    public function getDoneAttemptsCount(Task $task, User $user): int;

    public function getContractorLastAttempt(User $contractor, Task $task): ?Attempt;

    public function getContractorDoneAttempts(User $contractor, Task $task): array;
}
