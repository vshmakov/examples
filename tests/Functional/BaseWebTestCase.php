<?php

namespace App\Tests\Functional;

use App\Request\ContentType;
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

    protected static function createAuthenticatedClient(string $username): Client
    {
        $client = static::createClient([], [
            self::TEST_AUTHENTICATION_HEADER_NAME => $username,
        ]);

        return $client;
    }

    /**
     * @return mixed
     */
    protected function ajaxGet(Client $client, string $url, array $parameters = [])
    {
        $client->xmlHttpRequest('GET', $url, $parameters);

        return json_decode($client->getResponse()->getContent(), true);
    }

    /**
     * @return mixed
     */
    protected function ajaxPut(Client $client, string $url, array $parameters)
    {
        $client->xmlHttpRequest(
            'PUT',
            $url,
            [],
            [],
            ['CONTENT_TYPE' => ContentType::JSON],
            json_encode($parameters)
        );

        return json_decode($client->getResponse()->getContent(), true);
    }
}
