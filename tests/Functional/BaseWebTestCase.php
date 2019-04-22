<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Request\Http\ContentType;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\DomCrawler\Crawler;

abstract class BaseWebTestCase extends WebTestCase
{
    public const TEST_AUTHENTICATION_HEADER_NAME = 'test_authentication_header';

    protected static function createAuthenticatedClient(string $username): Client
    {
        $client = static::createClient([], [
            self::TEST_AUTHENTICATION_HEADER_NAME => $username,
        ]);

        return $client;
    }

    protected function wrapItemsInArray(array $items): array
    {
        return array_map(
            function ($item): array {
                return [$item];
            },
            $items
        );
    }

    protected function getTrimmedText(Crawler $crawler): string
    {
        return trim($crawler->text());
    }

    protected function assertResponseIsSuccessful(Client $client): void
    {
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    protected function assertRedirectionToLoginPage(Client $client): void
    {
        $this->assertTrue($client->getResponse()->isRedirection());
        $this->assertRegExp('#/login$#', $client->getResponse()->headers->get('location'));
    }

    protected function assertRedirectionLocationMatch(string $expression, Client $client): array
    {
        $targetUrl = $client->getResponse()->headers->get('location');
        $this->assertTrue((bool) preg_match($expression, $targetUrl, $matches));

        return $matches;
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
