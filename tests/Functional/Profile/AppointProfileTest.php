<?php

namespace App\Tests\Functional\Profile;

use App\DataFixtures\Attempt\ProfileFixtures;
use App\Tests\Functional\BaseWebTestCase;
use  Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;

final class AppointProfileTest extends BaseWebTestCase
{
    /** @var Client */
    private static $unauthenticatedClient;

    public static function setUpBeforeClass(): void
    {
        self::$unauthenticatedClient = self::createClient();
    }

    /**
     * @test
     */
    public function guestCannotAppointProfiles(): void
    {
        $page = self::$unauthenticatedClient->request('GET', '/profile/');
        $this->assertResponseIsSuccessful(self::$unauthenticatedClient);

        $publicProfiles = $page->filter('#public-profiles tbody tr');
        $defaultGuestProfile = $publicProfiles->first();
        $this->assertSame(ProfileFixtures::GUEST_PROFILE_DESCRIPTION, trim($defaultGuestProfile->filter('.profile-description')->text()));
        $this->assertSame('Да', $defaultGuestProfile->filter('.is-current-profile')->text());

        $this->assertGreaterThan(1, $publicProfiles->count());
        $publicProfiles->each(function (Crawler $profile): void {
            $this->assertSame('-', trim($profile->filter('.profile-actions')->text()));
        });
    }
}
