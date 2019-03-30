<?php

namespace App\Tests\Functional\Profile\Traits;

use Symfony\Component\DomCrawler\Crawler;

trait DOMElementsTrait
{
    /** @var Crawler */
    private static $profileIndexCrawler;

    private function getNotAbleAppointProfilesMessageCrawler(Crawler $profileIndexPageCrawler): Crawler
    {
        return $profileIndexPageCrawler->filter('#not-able-appoint-profiles-message');
    }

    private function getFirstPublicProfileCrawler(Crawler $profileIndexPageCrawler): Crawler
    {
        return $profileIndexPageCrawler->filter('#public-profiles tbody tr:first-child');
    }

    private function getNewProfileLinkCrawler(Crawler $profileIndexPageCrawler): Crawler
    {
        return $profileIndexPageCrawler->filter('a[href="/profile/new/"]');
    }

    private function getProfileDescription(Crawler $profileCrawler): string
    {
        return $profileCrawler->filter('.profile-description a')->text();
    }
}
