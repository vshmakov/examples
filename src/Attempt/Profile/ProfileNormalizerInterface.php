<?php

declare(strict_types=1);

namespace App\Attempt\Profile;

use App\Entity\Profile;

interface ProfileNormalizerInterface
{
    public function normalize(Profile $profile): void;
}
