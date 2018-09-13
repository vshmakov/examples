<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class IndexControllerTest extends WebTestCase
{
    public function testRequest()
    {
        $client = static::createClient();

        $client->request('POST', '/api/request/yandex');

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
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
        ];

        foreach ($routes as $status => $urlList) {
            foreach ($urlList as $url) {
                $client->request('GET', $url);
            }
            $this->assertEquals($status, $client->getResponse()->getStatusCode());
        }
    }
}
