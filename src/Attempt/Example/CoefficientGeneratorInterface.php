<?php

namespace App\Attempt\Example;

use App\Entity\Example;
use App\Entity\Settings;

interface CoefficientGeneratorInterface
{
    /**
     * @param Example[] $previousExamples
     */
    public function getUniqueCoefficient(Example $example, array $previousExamples): int;

    public function getAmplitudeCoefficient(Example $example, Settings $settings): int;
}
