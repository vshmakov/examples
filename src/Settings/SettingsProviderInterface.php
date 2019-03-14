<?php

namespace App\Settings;

use App\Entity\Profile;
use App\Entity\Settings;

interface SettingsProviderInterface
{
    public function getSettingsByProfileOrCreate(Profile $profile): Settings;
}
