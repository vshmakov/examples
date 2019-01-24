<?php

namespace App\DataNormalizer\ArithmeticFunctionsSettings;

use App\Entity\Attempt\Settings\ArithmeticFunctionsSettingsInterface;

interface NormalizerInterface
{
    public function normalize(ArithmeticFunctionsSettingsInterface $arithmeticFunctionsSettings): ArithmeticFunctionsSettingsInterface;
}
