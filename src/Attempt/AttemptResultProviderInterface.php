<?php

declare(strict_types=1);

namespace App\Attempt;

use App\Entity\Attempt;

interface AttemptResultProviderInterface
{
    public function updateAttemptResult(Attempt $attempt): void;
}
