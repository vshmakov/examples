<?php

namespace App\Attempt\Example;

use App\Entity\Example;

final class Solver implements ExampleSolverInterface
{
    public function isRight(float $answer, Example $example): bool
    {
        return $answer === $this->solve($example);
    }

    private function solve(Example $example): ?float
    {
        $first = $example->getFirst();
        $second = $example->getSecond();

        switch ($example->getSign()) {
            case 1:
                return $first + $second;
                break;

            case 2:
                return $first - $second;
                break;

            case 3:
                return $first * $second;
                break;

            case 4:
                return $second ? $first / $second : null;
                break;

            default:
                throw new \UnexpectedValueException();
                break;
        }
    }
}
