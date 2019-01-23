<?php

namespace App\Entity\Attempt\Settings;

interface ArithmeticFunctionsSettingsInterface
{
    //Subtraction
    public function getMinimumMinuend(): float;

    public function getMaximumMinuend(): float;

    public function getMinimumSubtrahend(): float;

    public function getMaximumSubtrahend(): float;

    public function getMinimumDifference(): float;

    public function getMaximumDifference(): float;

    //Multiplication
    public function getMinimumMultiplicands(): float;

    public function getMaximumMultiplicands(): float;

    public function getMinimumMultiplier(): float;

    public function getMaximumMultiplier(): float;

    public function getMinimumProduct(): float;

    public function getMaximumProduct(): float;

    //Division
    public function getMinimumDividend(): float;

    public function getMaximumDividend(): float;

    public function getMinimumDivisor(): float;

    public function getMaximumDivisor(): float;

    public function getMinimumQuotient(): float;

    public function getMaximumQuotient(): float;
}
