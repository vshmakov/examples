<?php

namespace App\Attempt;

use App\Entity\Attempt;
use App\Entity\Task;
use App\Entity\User;

interface AttemptProviderInterface
{
    public function getLastAttempt(): ?Attempt;

    public function getDoneAttemptsCount(Task $task, User $user): int;
}
