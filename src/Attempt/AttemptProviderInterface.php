<?php

namespace App\Attempt;

use App\Entity\Attempt;

interface AttemptProviderInterface
{
    public function getLastAttempt(): ?Attempt;
}
