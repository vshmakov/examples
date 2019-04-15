<?php

namespace App\Attempt;

use App\Entity\Attempt;
use App\Response\AttemptResponse;

interface AttemptResponseFactoryInterface
{
    public function createAttemptResponse(Attempt $attempt): AttemptResponse;
}
