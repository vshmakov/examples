<?php

namespace App\Attempt;

use App\Entity\Attempt;

interface AttemptCreatorInterface
{
    public function createAttempt(): Attempt;
}
