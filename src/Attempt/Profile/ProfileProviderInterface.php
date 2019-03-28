<?php

namespace App\Attempt\Profile;

use App\Entity\Profile;

interface ProfileProviderInterface
{
    public function getPublicProfiles(): array;

    public function getUserProfiles(): array;

    public function getCurrentProfile(): Profile;

    public function isCurrentProfile(Profile $profile): bool;
}
