<?php

namespace App\Attempt\Example;

use App\Entity\Example;

interface ExampleSolverInterface
{
    public function isRight(float $answer, Example $example): bool;
}
