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
echo $client->getResponse()->getContent();
    }
}