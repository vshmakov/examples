<?php

declare(strict_types=1);

namespace App\User\SocialAccount\Credentials;

interface SocialAccountCredentialsProviderInterface
{
    public function getSocialAccountCredentials(string $token): array;
}
