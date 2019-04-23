<?php

namespace App\User\SocialAccount\Credentials;

interface SocialAccountCredentialsProviderInterface
{
    public function getSocialAccountCredentials(string $token): array;
}
