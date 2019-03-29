<?php

namespace App\Attempt\Settings;

use App\Entity\Settings;

interface SettingsProviderInterface
{
    public function getOrCreateSettingsByCurrentUserProfile(): Settings;
}
