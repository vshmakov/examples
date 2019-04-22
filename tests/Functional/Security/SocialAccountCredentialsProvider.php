<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use App\User\SocialAccount\Credentials\SocialAccountCredentialsProviderInterface;

final class SocialAccountCredentialsProvider implements SocialAccountCredentialsProviderInterface
{
    public function getSocialAccountCredentials(string $token): array
    {
        return [
            'network' => 'my_network',
            'uid' => 123456,
        ];
    }
}
