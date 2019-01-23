<?php

namespace App\Entity\Attempt\Settings;

interface SubtractionSettingsInterface
{
    public function getMinimumMinuend(): float;

    public function setMinimumMinuend(float $minimumMinuend): void;

    public function getMaximumMinuend(): float;

    public function setMaximumMinuend(float $maximumMinuend): void;

    public function getMinimumSubtrahend(): float;

    public function setMinimumSubtrahend(float $minimumSubtrahend): void;

    public function getMaximumSubtrahend(): float;

    public function setMaximumSubtrahend(float $maximumSubtrahend): void;

    public function getMinimumDifference(): float;

    public function setMinimumDifference(float $minimumDifference): void;

    public function getMaximumDifference(): float;

    public function setMaximumDifference(float $maximumDifference): void;
}
