<?php

namespace App\Response;

use App\Entity\Attempt;

interface AttemptResponseProviderInterface
{
    public function createAttemptResponse(Attempt $attempt): AttemptResponse;
}
