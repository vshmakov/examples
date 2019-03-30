<?php

namespace App\Tests\Functional\Profile;

use App\DataFixtures\Attempt\ProfileFixtures;
use App\DataFixtures\UserFixtures;
use App\Tests\Functional\BaseWebTestCase;
use App\Tests\Functional\Profile\Traits\DOMElementsTrait;
use  Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;

final class StudentManipulateProfilesTest extends BaseWebTestCase
{
    use  DOMElementsTrait;

    private const  PROFILE_DESCRIPTION = 'My new profile description';

    /** @var Client */
    private static $studentClient;

    public static function setUpBeforeClass(): void
    {
        self::$studentClient = self::createAuthenticatedClient(UserFixtures::STUDENT_USERNAME);
    }

    /**
     * @test
     */
    public function studentEntersToProfileIndexPage(): Crawler
    {
        $profileIndexCrawler = self::$studentClient->request('GET', '/profile/');
        $this->assertResponseIsSuccessful(self::$studentClient);

        return $profileIndexCrawler;
    }

    /**
     * @test
     * @depends  studentEntersToProfileIndexPage
     */
    public function studentSeesCreateProfileButton(Crawler $profileIndexPageCrawler): Crawler
    {
        $newProfileLinkCrawler = $this->getNewProfileLinkCrawler($profileIndexPageCrawler);
        $this->assertNotEmpty($newProfileLinkCrawler);

        return $newProfileLinkCrawler;
    }

    /**
     * @test
     * @depends  studentSeesCreateProfileButton
     */
    public function studentCreatesNewProfile(Crawler $newProfileLinkCrawler): int
    {
        $newProfilePageCrawler = self::$studentClient->click($newProfileLinkCrawler->link());
        $this->assertResponseIsSuccessful(self::$studentClient);
        $newProfileForm = $newProfilePageCrawler->filter('form')->form();
        self::$studentClient->submit($newProfileForm, [
            'profile[description]' => self::PROFILE_DESCRIPTION,
        ]);

        return $this->getProfileIdFromRedirectingToEditPage(self::$studentClient);
    }

    /**
     * @test
     * @depends  studentCreatesNewProfile
     */
    public function studentCopysProfile(int $newProfileId): Crawler
    {
        $editProfilePageCrawler = self::$studentClient->followRedirect();
        $editProfileForm = $editProfilePageCrawler->selectButton('Копировать')->form();
        $this->assertSame(self::PROFILE_DESCRIPTION, $editProfileForm->get('profile[description]')->getValue());
        $this->assertCurrentProfile($editProfilePageCrawler);

        self::$studentClient->submit($editProfileForm);
        $copiedProfileId = $this->getProfileIdFromRedirectingToEditPage(self::$studentClient);
        $this->assertNotSame($newProfileId, $copiedProfileId);

        return self::$studentClient->followRedirect();
    }

    /**
     * @test
     * @depends  studentCopysProfile
     */
    public function studentSavesCopiedProfile(Crawler $copiedProfileEditPageCrawler): Crawler
    {
        $this->assertCurrentProfile($copiedProfileEditPageCrawler);
        $editProfileForm = $copiedProfileEditPageCrawler->selectButton('Сохранить')->form();
        self::$studentClient->submit($editProfileForm);

        return self::$studentClient->followRedirect();
    }

    /**
     * @test
     * @depends  studentSavesCopiedProfile
     */
    public function studentShowsProfiles(): void
    {
        $this->studentEntersToProfileIndexPage()
            ->filter('tbody .profile-description a')
            ->each(function (Crawler $showProfileLinkCrawler): void {
                self::$studentClient->click($showProfileLinkCrawler->link());
                $this->assertResponseIsSuccessful(self::$studentClient);
            });
    }

    /**
     * @test
     * @depends studentSavesCopiedProfile
     */
    public function studentStartsNewAttemptWithCopiedProfile(Crawler $showProfileCrawler): void
    {
        $startNewAttemptLink = $showProfileCrawler->selectLink('Начать новую попытку с этим профилем')->link();
        self::$studentClient->click($startNewAttemptLink);
        $solveAttemptPageCrawler = self::$studentClient->followRedirect();
        $showAttemptSettingsLinkCrawler = $solveAttemptPageCrawler->filter('#profile-settings a');
        $this->assertSame(self::PROFILE_DESCRIPTION, $showAttemptSettingsLinkCrawler->text());
    }

    /**
     * @test
     * @depends studentStartsNewAttemptWithCopiedProfile
     */
    public function studentRemovesPersonalProfiles(): Crawler
    {
        $this->studentEntersToProfileIndexPage()
            ->filter('#user-profiles form')
            ->each(function (Crawler $removeProfileFormCrawler): void {
                self::$studentClient->submit($removeProfileFormCrawler->form());
                $this->assertTrue(self::$studentClient->getResponse()->isRedirect('/profile/'));
            });

        $profileIndexPageCrawler = $this->studentEntersToProfileIndexPage();
        $this->assertEmpty($profileIndexPageCrawler->filter('#user-profiles tbody tr'));

        return $profileIndexPageCrawler;
    }

    /**
     * @test
     * @depends  studentRemovesPersonalProfiles
     */
    public function studentHasTestProfileByDefault(Crawler $profileIndexPageCrawler): Crawler
    {
        $firstPublicProfileCrawler = $this->getFirstPublicProfileCrawler($profileIndexPageCrawler);
        $this->assertSame('Да', $firstPublicProfileCrawler->filter('.is-current-profile')->text());
        $descriptionCrawler = $firstPublicProfileCrawler->filter('.profile-description');
        $this->assertSame(ProfileFixtures::GUEST_PROFILE_DESCRIPTION, $this->getTrimmedText($descriptionCrawler));

        return $profileIndexPageCrawler;
    }

    /**
     * @test
     * @depends  studentHasTestProfileByDefault
     */
    public function studentDoesNotSeeNotAbleAppointProfilesMessage(Crawler $profileIndexPageCrawler): void
    {
        $notAbleAppointProfilesMessageCrawler = $this->getNotAbleAppointProfilesMessageCrawler($profileIndexPageCrawler);
        $this->assertEmpty($notAbleAppointProfilesMessageCrawler);
    }

    private function assertCurrentProfile(Crawler $showOrEditPageCrawler): void
    {
        $this->assertRegExp('#\(Текущий\)#', $showOrEditPageCrawler->filter('h1')->text());
    }

    private function getProfileIdFromRedirectingToEditPage(Client $client): int
    {
        $targetUrl = $client->getResponse()->headers->get('location');
        $this->assertTrue((bool) preg_match('#/profile/(?<profileId>\d+)/edit/#', $targetUrl, $matches));

        return $matches['profileId'];
    }
}
