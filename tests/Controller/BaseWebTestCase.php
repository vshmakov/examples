<?php

namespace App\Tests\Controller;

use App\DataFixtures\UserFixtures;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class BaseWebTestCase extends WebTestCase
{
    public const TEST_AUTHENTICATION_HEADER_NAME = 'test_authentication_header';

    protected function wrapItemsInArray(array $items): array
    {
        return array_map(
            function ($item): array {
                return [$item];
            },
            $items
        );
    }

    protected static function createAuthenticatedClient(string $username = UserFixtures::STUDENT_USERNAME): Client
    {
        $client = static::createClient([], [
            self::TEST_AUTHENTICATION_HEADER_NAME => $username,
        ]);

        return $client;
    }
}
