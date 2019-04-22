<?php

declare(strict_types=1);

namespace App\Attempt\Profile;

use App\Entity\Profile;
use App\Entity\Settings;

interface ProfileProviderInterface
{
    public function getPublicProfiles(): array;

    public function getCurrentUserProfiles(): array;

    public function getCurrentProfile(): Profile;

    public function isCurrentProfile(Profile $profile): bool;

    public function getSettingsOrDefaultProfile(Settings $settings): Profile;
}
