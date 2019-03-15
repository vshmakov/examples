<?php

namespace App\Attempt;

use App\Entity\Attempt;

interface AttemptResultProviderInterface
{
    public function updateAttemptResult(Attempt $attempt): void;
}
