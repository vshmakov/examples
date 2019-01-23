<?php

namespace App\Entity\Attempt\Settings;

interface DivisionSettingsInterface
{
    public function getMinimumDividend(): float;

    public function setMinimumDividend(float $minimumDividend): void;

    public function getMaximumDividend(): float;

    public function setMaximumDividend(float $maximumDividend): void;

    public function getMinimumDivisor(): float;

    public function setMinimumDivisor(float $minimumDivisor): void;

    public function getMaximumDivisor(): float;

    public function setMaximumDivisor(float $maximumDivisor): void;

    public function getMinimumQuotient(): float;

    public function setMinimumQuotient(float $minimumQuotient): void;

    public function getMaximumQuotient(): float;

    public function setMaximumQuotient(float $maximumQuotient): void;
}
