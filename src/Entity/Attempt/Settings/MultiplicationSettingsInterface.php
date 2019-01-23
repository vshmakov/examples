<?php

namespace App\Entity\Attempt\Settings;

interface MultiplicationSettingsInterface
{
    public function getMinimumMultiplicands(): float;

    public function setMinimumMultiplicands(float $minimumMultiplicands): void;

    public function getMaximumMultiplicands(): float;

    public function setMaximumMultiplicands(float $maximumMultiplicands): void;

    public function getMinimumMultiplier(): float;

    public function setMinimumMultiplier(float $minimumMultiplier): void;

    public function getMaximumMultiplier(): float;

    public function setMaximumMultiplier(float $maximumMultiplier): void;

    public function getMinimumProduct(): float;

    public function setMinimumProduct(float $minimumProduct): void;

    public function getMaximumProduct(): float;

    public function setMaximumProduct(float $maximumProduct): void;
}
