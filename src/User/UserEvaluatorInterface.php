<?php

namespace App\User;

use App\Entity\User;

interface UserEvaluatorInterface
{
    public function getActivityCoefficient(User $user): int;
}
