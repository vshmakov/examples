<?php

namespace App\Attempt;

use App\Entity\Attempt;
use App\Entity\Task;
use App\Entity\User;

interface AttemptFactoryInterface
{
    public function createCurrentUserAttempt(): Attempt;

    public function createCurrentUserSolvesTaskAttempt(Task $task): Attempt;

    public function createUserSolvesTaskAttempt(Task $task, User $user): Attempt;
}
