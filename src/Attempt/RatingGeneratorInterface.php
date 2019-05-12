<?php

namespace App\Attempt;

interface RatingGeneratorInterface
{
    public function generateRating(int $errorsCount, int $examplesCount): int;
}
