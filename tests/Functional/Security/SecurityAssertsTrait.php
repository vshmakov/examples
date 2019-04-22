<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;

trait SecurityAssertsTrait
{
    private function assertSignedIn(Client $client): Crawler
    {
        $this->assertRedirectionToHomepage($client);
        $homepageCrawler = $client->followRedirect();
        $this->assertRegExp('#Главная страница#', $homepageCrawler->filter('head title')->text());

        return $homepageCrawler;
    }

    private function assertRedirectionToHomepage(Client $client): void
    {
        $isRedirectLocation = function (string $url) use ($client): bool {
            return $client->getResponse()->isRedirect($url);
        };
        $this->assertTrue($isRedirectLocation('http://localhost/') or $isRedirectLocation('/'));
    }
}
