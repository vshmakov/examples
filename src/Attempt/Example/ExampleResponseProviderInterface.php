<?php

namespace App\Attempt\Example;

use App\Attempt\Example\Number\NumberProviderInterface;
use App\Entity\Attempt;
use App\Entity\Example;
use App\Response\ExampleResponse;

interface ExampleResponseProviderInterface
{
    public function createExampleResponse(Example $example, NumberProviderInterface $numberProvider = null): ExampleResponse;

    public function createSolvingExampleResponse(Attempt $attempt): ?ExampleResponse;
}
