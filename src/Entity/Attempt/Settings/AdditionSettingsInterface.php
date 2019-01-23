<?php

namespace App\Entity\Attempt\Settings;

interface AdditionSettingsInterface
{
    public function getMinimumFirstAddend(): float;

    public function setMinimumFirstAddend(): float;

    public function getMaximumFirstAddend(): float;

    public function setMaximumFirstAddend(): float;

    public function getMinimumSecondAddend(): float;

    public function setMinimumSecondAddend(): float;

    public function getMaximumSecondAddend(): float;

    public function setMaximumSecondAddend(): float;

    public function getMinimumSum(): float;

    public function getMaximumSum(): float;
}
