<?php

namespace App\Attempt\Example;

use App\Entity\Example;
use App\Entity\Settings;
use App\Object\ObjectAccessor;
use Webmozart\Assert\Assert;

final class ExampleCoefficientGenerator implements CoefficientGeneratorInterface
{
    /** @var ExampleSolverInterface */
    private $exampleSolver;

    public function __construct(ExampleSolverInterface $exampleSolver)
    {
        $this->exampleSolver = $exampleSolver;
    }

    /**
     * @param Example[] $previousExamples
     */
    public function getUniqueCoefficient(Example $example, array $previousExamples): int
    {
        $uniqueCoefficient = 1;
        $previousExamplesCount = \count($previousExamples) ?: 1;

        foreach ($previousExamples as $previousExample) {
            if (ObjectAccessor::isSame($example, $previousExample, ['first', 'second', 'sign'])) {
                $uniqueCoefficient -= 60 / 100 / $previousExamplesCount;
            }

            if ($previousExample->isRight() && $this->exampleSolver->isRight($previousExample->getAnswer(), $example)) {
                $uniqueCoefficient -= 40 / 100 / $previousExamplesCount;
            }
        }

        return $this->toIntegerValue($uniqueCoefficient);
    }

    public function getAmplitudeCoefficient(Example $example, Settings $settings): int
    {
        $getPropertySettings = function (bool $isSecond, bool $isMax) use ($example, $settings): float {
            $propertyPath = sprintf(
                '%s_%s_%s',
                Example::ACTION_NAMES[$example->getSign()],
                $isSecond ? 's' : 'f',
                $isMax ? 'max' : 'min'
            );

            return ObjectAccessor::getValue($settings, $propertyPath);
        };

        $amplitudeCoefficient = 0;
        $amplitudeCoefficient += $this->getPercentsAmplitude($example->getFirst(), $getPropertySettings(false, false), $getPropertySettings(false, true));
        $amplitudeCoefficient += $this->getPercentsAmplitude($example->getSecond(), $getPropertySettings(true, false), $getPropertySettings(true, true));
        $amplitudeCoefficient = 1 - ($amplitudeCoefficient / 2 / 100);

        return $this->toIntegerValue($amplitudeCoefficient * 20 / 100);
    }

    private function getPercentsAmplitude(float $number, float $min, float $max): float
    {
        Assert::notSame($min, $max);
        $middle = ($max - $min) / 2;
        $amplitude = abs($middle - $number);
        $percentsAmplitude = round($amplitude / abs($max - $min) * 100);

        return $percentsAmplitude;
    }

    private function toIntegerValue(float $coefficient): int
    {
        return (int) round($coefficient * 10 ** 4);
    }
}
