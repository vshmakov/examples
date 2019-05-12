<?php

namespace App\User;

use App\Entity\Task;
use App\Entity\User;

interface UserEvaluatorInterface
{
    public function getActivityCoefficient(User $user): int;

    public function getTaskRating(User $user, Task $task): ?int;
}
