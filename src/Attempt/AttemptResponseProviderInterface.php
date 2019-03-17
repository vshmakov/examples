<?php

namespace App\Attempt;

use App\Entity\Attempt;
use App\Response\AttemptResponse;

interface AttemptResponseProviderInterface
{
    public function createAttemptResponse(Attempt $attempt): AttemptResponse;
}
