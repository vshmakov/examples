<?php

namespace App\DataNormalizer\Settings;

use App\Entity\BaseProfile as Settings;

interface SettingsNormalizerInterface
{
    public function normalize(Settings $settings): void;
}
