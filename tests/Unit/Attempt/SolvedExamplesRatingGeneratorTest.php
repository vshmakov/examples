<?php

namespace App\Tests\Unit\Attempt;

use App\Attempt\RatingGeneratorInterface;
use App\Attempt\SolvedExamplesRatingGenerator;
use PHPUnit\Framework\TestCase;

final class SolvedExamplesRatingGeneratorTest extends TestCase
{
    /** @var RatingGeneratorInterface */
    private $solvedExamplesRatingGenerator;

    protected function setUp(): void
    {
        $this->solvedExamplesRatingGenerator = new SolvedExamplesRatingGenerator();
    }

    public function ratingProvider(): array
    {
        return [
            [2, 1, 1],
            [1, 5, 4],
            [1, 20, 5],
            [4, 20, 3],
            [6, 20, 2],
            [2, 30, 4],
            [6, 50, 3],
            [2, 80, 5],
            [6, 100, 4],
        ];
    }

    /**
     * @test
     * @dataProvider  ratingProvider
     */
    public function serviceGeneratesRating(int $errorsCount, int $examplesCount, int $rating): void
    {
        $this->assertSame(
            $rating,
            $this->solvedExamplesRatingGenerator->generateRating($errorsCount, $examplesCount)
        );
    }
}
