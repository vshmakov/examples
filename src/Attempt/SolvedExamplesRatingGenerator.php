<?php

namespace App\Attempt;

final class SolvedExamplesRatingGenerator implements RatingGeneratorInterface
{
    private const COEFFICIENTS = [
        5 => [2 => 2, 3, 4, 5],
        10 => [2 => 5, 6, 8, 9],
        20 => [2 => 14, 15, 17, 19],
        50 => [2 => 40, 42, 45, 48],
        100 => [2 => 90, 92, 94, 97],
    ];

    public function generateRating(int $errorsCount, int $totalExamplesCount): int
    {
        if ($errorsCount === $totalExamplesCount) {
            return 0;
        }

        $rightExamplesCount = $totalExamplesCount - $errorsCount;
        $resultRating = 1;

        foreach ($this->getActualCoefficients($totalExamplesCount) as $rating => $validRightExamplesCount) {
            if ($rightExamplesCount >= $validRightExamplesCount) {
                $resultRating = $rating;
            }
        }

        return $resultRating;
    }

    private function getActualCoefficients(int $examplesCount): array
    {
        foreach (array_keys(self::COEFFICIENTS) as $totalExamplesCount) {
            if ($examplesCount <= $totalExamplesCount) {
                break;
            }
        }

        return array_map(function (int $rightExamplesCount) use ($examplesCount, $totalExamplesCount): int {
            return (int) round($rightExamplesCount * $examplesCount / $totalExamplesCount);
        }, self::COEFFICIENTS[$totalExamplesCount]);
    }
}
