<?php

namespace App\DataNormalizer\ArithmeticFunctionsSettings;

use App\Entity\Attempt\Settings\ArithmeticFunctionsSettingsInterface;

class ArithmeticFunctionsSettingsNormalizer implements NormalizerInterface
{
    public function normalize(ArithmeticFunctionsSettingsInterface $arithmeticFunctionsSettings): ArithmeticFunctionsSettingsInterface
    {
        return $arithmeticFunctionsSettings;
    }
}
