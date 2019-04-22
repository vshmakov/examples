<?php

declare(strict_types=1);

namespace App\Attempt\Profile;

use App\Entity\Profile;

interface ProfileInitializerInterface
{
    public function initializeNewProfile(): Profile;
}
