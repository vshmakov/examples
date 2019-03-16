<?php

namespace App\Attempt\Example;

use App\Entity\Attempt;
use App\Entity\Example;
use App\Response\ExampleResponse;

interface ExampleResponseProviderInterface
{
    public function createExampleResponse(Example $example): ExampleResponse;

    public function createSolvingExampleResponse(Attempt $attempt): ?ExampleResponse;
}
