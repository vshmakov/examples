<?php

namespace App\Tests\Functional\Profile;

use App\DataFixtures\Attempt\ProfileFixtures;
use App\Request\Http\Method;
use App\Tests\Functional\BaseWebTestCase;
use App\Tests\Functional\Profile\Traits\DOMElementsTrait;
use  Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;

final class GuestManipulateProfilesTest extends BaseWebTestCase
{
    use  DOMElementsTrait;

    /** @var Client */
    private static $unauthenticatedClient;

    public static function setUpBeforeClass(): void
    {
        self::$unauthenticatedClient = self::createClient();
    }

    /**
     * @test
     */
    public function guestEntersToProfileIndexPage(): Crawler
    {
        $profileIndexCrawler = self::$unauthenticatedClient->request('GET', '/profile/');
        $this->assertResponseIsSuccessful(self::$unauthenticatedClient);

        return $profileIndexCrawler;
    }

    /**
     * @test
     * @depends  guestEntersToProfileIndexPage
     */
    public function guestShowsNoActionsOfProfiles(Crawler $profileIndexPageCrawler): void
    {
        $profileIndexPageCrawler->filter('tbody .profile-actions')
            ->each(function (Crawler $actionsCrawler): void {
                $this->assertSame('-', $this->getTrimmedText($actionsCrawler));
            });
    }

    /**
     * @test
     * @depends  guestEntersToProfileIndexPage
     */
    public function guestHasTestProfileByDefault(Crawler $profileIndexPageCrawler): void
    {
        $firstPublicProfileCrawler = $this->getFirstPublicProfileCrawler($profileIndexPageCrawler);
        $this->assertSame('Да', $this->getTrimmedText($firstPublicProfileCrawler->filter('.is-current-profile')));
        $descriptionCrawler = $firstPublicProfileCrawler->filter('.profile-description');
        $this->assertSame(ProfileFixtures::GUEST_PROFILE_DESCRIPTION, $this->getTrimmedText($descriptionCrawler));
    }

    /**
     * @test
     * @depends  guestEntersToProfileIndexPage
     */
    public function guestShowsProfiles(Crawler $profileIndexPageCrawler): void
    {
        $profileIndexPageCrawler->filter('tbody .profile-description a')
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
    public function guestCanNotManipulateProfiles(string $url, string $method, Crawler $profileIndexPageCrawler): void
    {
        $showProfileLink = $profileIndexPageCrawler->filter('#public-profiles tbody tr:nth-child(2) a');
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
    public function guestDoesNotSeeCreateProfileButton(Crawler $profileIndexPageCrawler): void
    {
        $newProfileLinkCrawler = $profileIndexPageCrawler->filter('a[href="/profile/new/"]');
        $this->assertEmpty($newProfileLinkCrawler);
    }

    /**
     * @test
     * @depends  guestEntersToProfileIndexPage
     */
    public function guestSeesNotAbleAppointProfilesMessage(Crawler $profileIndexPageCrawler): void
    {
        $notAbleAppointProfilesMessageCrawler = $this->getNotAbleAppointProfilesMessageCrawler($profileIndexPageCrawler);
        $this->assertNotEmpty($notAbleAppointProfilesMessageCrawler);
    }
}
