<?php

declare(strict_types=1);

namespace App\Security\Ulogin;

use App\Entity\User\SocialAccount;

interface SocialAccountProviderInterface
{
    public function getSocialAccount(string $token): ?SocialAccount;
}
