<?php

namespace App\Service;

class ExampleManager
{
    public static function solve(float $first, float $second, int $sign): ?float
    {
        switch ($sign) {
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
        }
    }

    public function isRight(float $first, float $second, int $sign, ? float $answer): bool
    {
        return \is_float($answer) && $answer === self::solve($first, $second, $sign);
    }

    public static function rating(int $answeredExamplesCount, int $errorsCount): int
    {
        $rightExamplesCount = $answeredExamplesCount - $errorsCount;
        $ratingRightExamplesCount = [];

        for ($i = 5; $i >= 1; --$i) {
            $prevRatingRightExamplesCount = 5 === $i ? $answeredExamplesCount : $ratingRightExamplesCount[$i + 1];
            $coefficient = 5 === $i ? 0.98 : 0.97;

            if ($answeredExamplesCount <= 50) {
                $coefficient = 5 === $i ? 0.96 : 0.94;
            }

            if ($answeredExamplesCount <= 30) {
                $coefficient = 5 === $i ? 0.97 : 0.92;
            }

            if ($answeredExamplesCount <= 15) {
                $coefficient = 5 === $i ? 0.94 : 0.88;
            }

            if ($answeredExamplesCount <= 9) {
                $coefficient = 5 === $i ? 1 : 0.85;
            }

            $ratingRightExamplesCount[$i] = (int) ($prevRatingRightExamplesCount * $coefficient);
        }

        $rating = 1;

        for ($i = 1; $i <= 5; ++$i) {
            if ($rightExamplesCount >= $ratingRightExamplesCount[$i]) {
                $rating = $i;
            }
        }

        return $rating;
    }

    public function getRandomExample(int $sign, array $settings, array $previousExamples): array
    {
        $actionName = $this->getActionName($sign);
        $maxQualityCoefficient = 0;
        $needUniqueAnswer = $this->getBooleanByPercentsProbability(80);
        $needMaxAmplitude = $this->getBooleanByPercentsProbability(70);

        foreach ($settings as $key => $item) {
            if (\is_float($item)) {
                $settings[$key] = (int) $item;
            }
        }
        for ($i = 1; $i <= 20; ++$i) {
            extract($this->$actionName($settings));
            $qualityCoefficient = $this->getExampleQualityCoefficient($first, $second, $sign, $settings, $previousExamples, $needUniqueAnswer, $needMaxAmplitude);

            if ($qualityCoefficient > $maxQualityCoefficient) {
                $maxQualityCoefficient = $qualityCoefficient;
                $example = $this->createExampleArray($first, $second) + ['sign' => $sign];
            }
        }

        return $example;
    }

    private function getBooleanByPercentsProbability(int $percentsProbability): bool
    {
        return $percentsProbability >= mt_rand(1, 100);
    }

    private function getExampleQualityCoefficient(float $first, float $second, int $sign, array $settings, array $previousExamples, bool $needUniqueAnswer, bool $needMaxAmplitude): float
    {
        $qualityCoefficient = 100;
        $uniqueExampleCoefficient = $uniqueAnswerCoefficient = $amplitudeCoefficient = 0;
        $previousExamplesCount = \count($previousExamples) ?: 1;

        foreach ($previousExamples as $example) {
            if ($example->getFirst() === $first && $example->getSecond() === $second && $example->getSign() === $sign) {
                $uniqueExampleCoefficient += 1 / $previousExamplesCount * 60;
            }

            if (self::isRight($first, $second, $sign, $example->getAnswer()) && $needUniqueAnswer) {
                $uniqueAnswerCoefficient += 1 / $previousExamplesCount * 30;
            }
        }

        if ($needMaxAmplitude) {
            $amplitudeCoefficient = ($this->getAmplitudeCoefficient($first, $second, $sign, $settings) * 10 / 100 / ($previousExamplesCount ** 0.2));
        }

        $qualityCoefficient -= $uniqueExampleCoefficient + $uniqueAnswerCoefficient + $amplitudeCoefficient;

        return $qualityCoefficient;
    }

    private function getAmplitudeCoefficient(float $first, float $second, int $sign, array $settings): float
    {
        $actionName = $this->getActionName($sign);

        foreach (['f', 's', ''] as $number) {
            foreach (['min', 'max'] as $restriction) {
                $variableName = $number.ucfirst($restriction);
                $$variableName = $settings[$actionName.ucfirst($number).ucfirst($restriction)];
            }
        }

        $amplitudeCoefficient = 0;
        $amplitudeCoefficient += $this->getPercentsAmplitude($first, $fMin, $fMax);
        $amplitudeCoefficient += $this->getPercentsAmplitude($second, $sMin, $sMax);
        $amplitudeCoefficient = round(($amplitudeCoefficient / 3) ** 0.7);

        return $amplitudeCoefficient;
    }

    private function getPercentsAmplitude(float $number, float $min, float $max): float
    {
        $middle = ($max - $min) / 2;
        $amplitude = ($middle - $number);
        $percentsAmplitude = round(abs($amplitude) / abs($middle) * 100);

        return $percentsAmplitude;
    }

    private function getActionName(int $sign): string
    {
        return [1 => 'add', 'sub', 'mult', 'div'][$sign];
    }

    public function getRandomSign(array $settings): int
    {
        $randomPercent = mt_rand(1, 100);
        $totalProbability = 0;
        $sign = 1;

        foreach ([1 => 'addPerc', 'subPerc', 'multPerc', 'divPerc'] as $signNumber => $signProbability) {
            $totalProbability += $settings[$signProbability];

            if ($randomPercent <= $totalProbability) {
                $sign = $signNumber;

                break;
            }
        }

        return $sign;
    }

    private function getValueBetween(float $value, float $min, float $max): float
    {
        return btwVal($value, $min, $max);
    }

    private function add(array $settings): array
    {
        extract($settings);
        $switch = $this->getBooleanByPercentsProbability((50));

        if ($switch) {
            $firstMin = btwVal($addFMin, $addFMax, $addMin - $addSMax);
            $firstMax = btwVal($addFMin, $addFMax, $addMax - $addSMin);
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

        return $this->createExampleArray($first, $second);
    }

    private function createExampleArray(float $first, float $second): array
    {
        return ['first' => $first, 'second' => $second];
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

        return $this->createExampleArray($first + $second, $second);
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

        return $this->createExampleArray($first * $second, $second);
    }
}
