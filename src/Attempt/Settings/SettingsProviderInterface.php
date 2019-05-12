<?php

namespace App\Attempt\Settings;

use App\Entity\Profile;
use App\Entity\Settings;

interface SettingsProviderInterface
{
    public function getOrCreateSettingsByCurrentUserProfile(): Settings;

    public function getOrCreateSettingsByProfile(Profile $profile): Settings;
}
