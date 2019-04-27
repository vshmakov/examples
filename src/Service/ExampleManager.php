<?php

namespace App\Service;

use App\Attempt\Example\CoefficientGeneratorInterface;
use App\Attempt\Example\ExampleGeneratorInterface;
use App\Entity\Example;
use App\Entity\Settings;
use App\Object\ObjectAccessor;
use App\Serializer\Group;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

final class ExampleManager implements ExampleGeneratorInterface
{
    private const  GENERATING_ATTEMPTS_COUNT = 10;

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var CoefficientGeneratorInterface */
    private $coefficientGenerator;

    public function __construct(NormalizerInterface $normalizer, CoefficientGeneratorInterface $coefficientGenerator)
    {
        $this->normalizer = $normalizer;
        $this->coefficientGenerator = $coefficientGenerator;
    }

    /**
     * @param Example[] $previousExamples
     */
    public function generate(Settings $settings, array $previousExamples): Example
    {
        $sign = $this->generateRandomSign($settings);
        $maxQualityCoefficient = 0;
        $needUniqueAnswer = $this->getBooleanByPercentsProbability(80);
        $needMaximumAmplitude = $this->getBooleanByPercentsProbability(50);
        $resultExample = null;

        for ($i = 1; $i <= self::GENERATING_ATTEMPTS_COUNT; ++$i) {
            $example = $this->createRandomExample($sign, $settings);
            $uniqueCoefficient = $amplitudeCoefficient = 0;

            if ($needUniqueAnswer) {
                $uniqueCoefficient = $this->coefficientGenerator->getUniqueCoefficient($example, $previousExamples);
            }

            if ($needMaximumAmplitude) {
                $amplitudeCoefficient = $this->coefficientGenerator->getAmplitudeCoefficient($example, $settings);
            }
            $qualityCoefficient = $uniqueCoefficient + $amplitudeCoefficient;

            if ($qualityCoefficient >= $maxQualityCoefficient) {
                $maxQualityCoefficient = $qualityCoefficient;
                $resultExample = $example;
            }
        }

        return $resultExample;
    }

    private function createRandomExample(int $sign, Settings $settings): Example
    {
        $exampleData = \call_user_func(
            [$this, Example::ACTION_NAMES[$sign]],
            $this->normalizer->normalize($settings, null, ['groups' => Group::MATHEMATICAL_SETTINGS])
        );

        return ObjectAccessor::initialize(Example::class, $exampleData + [
                'sign' => $sign,
            ]);
    }

    private function generateRandomSign(Settings $settings): int
    {
        $randomPercent = mt_rand(1, 100);
        $totalProbability = 0;
        $sign = 1;

        foreach ([1 => 'addPerc', 'subPerc', 'multPerc', 'divPerc'] as $signNumber => $signProbability) {
            $totalProbability += ObjectAccessor::getValue($settings, $signProbability);

            if ($randomPercent <= $totalProbability) {
                $sign = $signNumber;

                break;
            }
        }

        return $sign;
    }

    private function createExampleData(float $first, float $second): array
    {
        return ['first' => $first, 'second' => $second];
    }

    private function add(array $settings): array
    {
        extract($settings);

        $firstMin = $this->getValueBetween($addFMin, $addMin - $addSMax, $addFMax);
        $firstMax = $this->getValueBetween($addFMax, $firstMin, $addMax - $addSMin);
        $first = mt_rand($firstMin, $firstMax);

        $secondMin = $this->getValueBetween($addSMin, $addMin - $first, $addSMax);
        $secondMax = $this->getValueBetween($addSMax, $secondMin, $addMax - $first);
        $second = mt_rand($secondMin, $secondMax);

        if ($this->getBooleanByPercentsProbability((40))) {
            $var = $first;
            $first = $second;
            $second = $var;
        }

        return $this->createExampleData($first, $second);
    }

    private function sub(array $settings): array
    {
        extract($settings);
        extract($this->add([
            'addFMin' => $subMin,
            'addFMax' => $subMax,
            'addSMin' => $subSMin,
            'addSMax' => $subSMax,
            'addMin' => $subFMin,
            'addMax' => $subFMax,
        ]));

        return $this->createExampleData($first + $second, $second);
    }

    private function mult(array $settings): array
    {
        extract($settings);

        $firstMin = $this->getValueBetween($multFMin, $multMin / ($multSMax ?: 1), $multFMax);
        $firstMax = $this->getValueBetween($multFMax, $firstMin, $multMax / ($multSMin ?: 1));
        $first = mt_rand($firstMin, $firstMax);

        $secondMin = $this->getValueBetween($multSMin, $multMin / ($first ?: 1), $multSMax);
        $secondMax = $this->getValueBetween($multSMax, $secondMin, $multMax / ($first ?: 1));
        $second = mt_rand($secondMin, $secondMax);

        if ($this->getBooleanByPercentsProbability(40)) {
            $var = $first;
            $first = $second;
            $second = $var;
        }

        return $this->createExampleData($first, $second);
    }

    private function div(array $settings): array
    {
        extract($settings);
        extract($this->mult([
            'multFMin' => $divMin,
            'multFMax' => $divMax,
            'multSMin' => $divSMin,
            'multSMax' => $divSMax,
            'multMin' => $divFMin,
            'multMax' => $divFMax,
        ]));

        return $this->createExampleData($first * $second, $second ?: 1);
    }

    private function getValueBetween(float $value, float $min, float $max): float
    {
        Assert::greaterThanEq($max, $min);

        if ($value > $max) {
            return $max;
        }

        if ($value < $min) {
            return $min;
        }

        return $value;
    }

    private function getBooleanByPercentsProbability(int $percentsProbability): bool
    {
        Assert::greaterThanEq($percentsProbability, 1);
        Assert::lessThanEq($percentsProbability, 99);

        return $percentsProbability > mt_rand(1, 100);
    }
}
