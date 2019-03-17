<?php

namespace App\Attempt\Profile;

use App\Entity\Profile;

interface NormalizerInterface
{
    public function normalize(Profile $profile): void;
}
