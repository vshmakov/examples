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
        $needMaximumAmplitude = $this->getBooleanByPercentsProbability(70);
        $resultExample = null;

        for ($i = 1; $i <= 20; ++$i) {
            $example = $this->createRandomExample($sign, $settings);
            $qualityCoefficient = 0;

            if ($needUniqueAnswer) {
                $qualityCoefficient += $this->coefficientGenerator->getUniqueCoefficient($example, $previousExamples);
            }

            if ($needMaximumAmplitude) {
                $qualityCoefficient += $this->coefficientGenerator->getAmplitudeCoefficient($example, $settings);
            }

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

        return ObjectAccessor::initialize(Example::class, $exampleData);
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
        $switch = $this->getBooleanByPercentsProbability((50));

        if ($switch) {
            $firstMin = $this->getValueBetween($addMin - $addSMax, $addFMin, $addFMax);
            $firstMax = $this->getValueBetween($addMax - $addSMin, $addFMin, $addFMax);
            $first = mt_rand($firstMin, $firstMax);

            $secondMin = $this->getValueBetween($addSMin, $addSMax, $addMin - $first);
            $secondMax = $this->getValueBetween($addSMin, $addSMax, $addMax - $first);
            $second = mt_rand($secondMin, $secondMax);
        } else {
            $secondMin = $this->getValueBetween($addSMin, $addSMax, $addMin - $addFMax);
            $secondMax = $this->getValueBetween($addSMin, $addSMax, $addMax - $addFMin);
            $second = mt_rand($secondMin, $secondMax);

            $firstMin = $this->getValueBetween($addFMin, $addFMax, $addMin - $second);
            $firstMax = $this->getValueBetween($addFMin, $addFMax, $addMax - $second);
            $first = mt_rand($firstMin, $firstMax);
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
        $switch = $this->getBooleanByPercentsProbability(50);

        if ($switch) {
            $firstMin = $this->getValueBetween($multFMin, $multFMax, $multMin / ($multSMax ?: 1));
            $firstMax = $this->getValueBetween($multFMin, $multFMax, $multMax / ($multSMin ?: 1));
            $first = mt_rand($firstMin, $firstMax);

            $secondMin = $this->getValueBetween($multSMin, $multSMax, $multMin / ($first ?: 1));
            $secondMax = $this->getValueBetween($multSMin, $multSMax, $multMax / ($first ?: 1));
            $second = mt_rand($secondMin, $secondMax);
        } else {
            $secondMin = $this->getValueBetween($multSMin, $multSMax, $multMin / ($multFMax ?: 1));
            $secondMax = $this->getValueBetween($multSMin, $multSMax, $multMax / ($multFMin ?: 1));
            $second = mt_rand($secondMin, $secondMax);

            $firstMin = $this->getValueBetween($multFMin, $multFMax, $multMin / ($second ?: 1));
            $firstMax = $this->getValueBetween($multFMin, $multFMax, $multMax / ($second ?: 1));
            $first = mt_rand($firstMin, $firstMax);
        }

        return $this->createExampleArray($first, $second);
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

        return $this->createExampleData($first * $second, $second);
    }

    private function getValueBetween(float $value, float $min, float $max): float
    {
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
