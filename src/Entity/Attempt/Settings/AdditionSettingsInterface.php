<?php

namespace App\Entity\Attempt\Settings;

interface AdditionSettingsInterface
{
    public function getMinimumFirstAddend(): float;

    public function setMinimumFirstAddend(float  $minimumFirstAddend): void;

    public function getMaximumFirstAddend(): float;

    public function setMaximumFirstAddend(float  $maximumFirstAddend): void;

    public function getMinimumSecondAddend(): float;

    public function setMinimumSecondAddend(float  $minimumSecondAddend): void;

    public function getMaximumSecondAddend(): float;

    public function setMaximumSecondAddend(float  $maximumSecondAddend): void;

    public function getMinimumSum(): float;

    public function setMinimumSum(float  $minimumSum): void;

    public function getMaximumSum(): float;

    public function setMaximumSum(float  $maximumSum): void;
}
