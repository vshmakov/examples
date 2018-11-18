<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class IndexControllerTest extends WebTestCase
{
    public function testRequest()
    {
        $client = static::createClient();

        $client->request('POST', '/api/request/yandex');

        $this->assertSame(400, $client->getResponse()->getStatusCode());
    }

    public function testAll()
    {
        $client = static::createClient();
        $routes = [
            200 => [
                '/',
                '/attempt',
                '/profile',
                '/login',
                '/register/',
            ],
302 => [
'/account/',
'/teacher/',
'/student/',
'/homework/',
'/task/',
],
        ];

        foreach ($routes as $status => $urlList) {
            foreach ($urlList as $url) {
                $client->request('GET', $url);
                $this->assertSame($status, $client->getResponse()->getStatusCode());
            }
        }
    }
}
