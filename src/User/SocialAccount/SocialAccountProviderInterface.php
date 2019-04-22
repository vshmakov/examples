<?php

declare(strict_types=1);

namespace App\User\SocialAccount;

use App\Entity\User\SocialAccount;

interface SocialAccountProviderInterface
{
    public function getSocialAccount(string $token): ?SocialAccount;
}
