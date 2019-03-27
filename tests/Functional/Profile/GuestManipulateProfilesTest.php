<?php

namespace App\Tests\Functional\Profile;

use App\DataFixtures\Attempt\ProfileFixtures;
use App\Request\Http\Method;
use App\Tests\Functional\BaseWebTestCase;
use  Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;

final class GuestManipulateProfilesTest extends BaseWebTestCase
{
    /** @var Client */
    private static $unauthenticatedClient;

    /** @var Crawler */
    private static $profileIndexCrawler;

    public static function setUpBeforeClass(): void
    {
        self::$unauthenticatedClient = self::createClient();
    }

    /**
     * @test
     */
    public function guestEntersToProfileIndexPage(): void
    {
        self::$profileIndexCrawler = self::$unauthenticatedClient->request('GET', '/profile/');
        $this->assertResponseIsSuccessful(self::$unauthenticatedClient);
    }

    /**
     * @test
     * @depends  guestEntersToProfileIndexPage
     */
    public function guestShowsNoActionsOfProfiles(): void
    {
        $profilesCrawler = self::$profileIndexCrawler->filter('tbody tr');
        $profilesCrawler->each(function (Crawler $profileCrawler): void {
            $this->assertSame('-', $this->getTrimmedText($profileCrawler->filter('.profile-actions')));
        });
    }

    /**
     * @test
     * @depends  guestEntersToProfileIndexPage
     */
    public function guestHasTestProfileByDefault(): void
    {
        $defaultGuestProfileCrawler = self::$profileIndexCrawler->filter('#public-profiles tbody tr')->first();
        $this->assertSame('Да', $defaultGuestProfileCrawler->filter('.is-current-profile')->text());
        $descriptionCrawler = $defaultGuestProfileCrawler->filter('.profile-description');
        $this->assertSame(ProfileFixtures::GUEST_PROFILE_DESCRIPTION, $this->getTrimmedText($descriptionCrawler));
    }

    /**
     * @test
     * @depends  guestEntersToProfileIndexPage
     */
    public function guestShowsProfiles(): void
    {
        self::$profileIndexCrawler
            ->filter('tbody .profile-description a')
            ->each(function (Crawler $showProfileLinkCrawler): void {
                self::$unauthenticatedClient->click($showProfileLinkCrawler->link());
                $this->assertResponseIsSuccessful(self::$unauthenticatedClient);
            });
    }

    public function manipulateProfileActionsProvider(): array
    {
        return [
            ['/profile/%s/edit/', Method::GET],
            ['/profile/%s/appoint/', Method::GET],
            ['/profile/%s/delete/', Method::DELETE],
        ];
    }

    /**
     * @test
     * @depends      guestEntersToProfileIndexPage
     * @dataProvider manipulateProfileActionsProvider
     */
    public function guestCanNotManipulateProfiles(string $url, string $method): void
    {
        $showProfileLink = self::$profileIndexCrawler->filter('#public-profiles tbody tr:nth-child(2) a');
        $this->assertNotSame(ProfileFixtures::GUEST_PROFILE_DESCRIPTION, $this->getTrimmedText($showProfileLink));
        $showProfileUrl = $showProfileLink->attr('href');
        $this->assertTrue((bool) preg_match('#/profile/(?<profileId>\d+)/#', $showProfileUrl, $matches));
        $profileId = $matches['profileId'];

        self::$unauthenticatedClient->request($method, sprintf($url, $profileId));
        $this->assertRedirectionToLoginPage(self::$unauthenticatedClient);
    }

    /**
     * @test
     * @depends  guestEntersToProfileIndexPage
     */
    public function guestDoesNotSeeCreateProfileButton(): void
    {
        $newProfileLinkCrawler = self::$profileIndexCrawler->filter('a[href="/profile/new/"]');
        $this->assertEmpty($newProfileLinkCrawler);
    }

    /**
     * @test
     * @depends  guestEntersToProfileIndexPage
     */
    public function guestSeesNotAbleAppointProfilesMessage(): void
    {
        $canNotAppointProfilesMessageCrawler = self::$profileIndexCrawler->filter('#cannot-appoint-profiles-message');
        $this->assertNotEmpty($canNotAppointProfilesMessageCrawler);
    }

    private function getTrimmedText(Crawler $crawler): string
    {
        return trim($crawler->text());
    }
}
