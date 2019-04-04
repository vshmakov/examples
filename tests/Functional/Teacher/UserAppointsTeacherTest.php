<?php

namespace App\Tests\Functional\Teacher;

use App\DataFixtures\UserFixtures;
use App\Request\Http\Method;
use App\Tests\Functional\BaseWebTestCase;
use  Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;

final class UserAppointsTeacherTest extends BaseWebTestCase
{
    /** @var Client */
    private static $userClient;

    public static function setUpBeforeClass(): void
    {
        self::$userClient = self::createAuthenticatedClient(UserFixtures::SIMPLE_USER_USERNAME);
    }

    /**
     * @test
     */
    public function userEntersToTeacherIndexPage(): Crawler
    {
        $teacherIndexPageCrawler = self::$userClient->request(Method::GET, '/teacher/');
        $this->assertResponseIsSuccessful(self::$userClient);

        return $teacherIndexPageCrawler;
    }

    /**
     * @test
     * @depends userEntersToTeacherIndexPage
     */
    public function userClicksChooseTeacherLink(Crawler $teacherIndexPageCrawler): Crawler
    {
        $chooseTeacherLinkCrawler = $this->getFirstTeacherCrawler($teacherIndexPageCrawler)->selectLink('Выбрать');
        $this->assertNotEmpty($chooseTeacherLinkCrawler);

        return self::$userClient->click($chooseTeacherLinkCrawler->link());
    }

    /**
     * @test
     * @depends userClicksChooseTeacherLink
     */
    public function userFillsAccount(Crawler $needFillAccountPageCrawler): Crawler
    {
        $this->assertResponseIsSuccessful(self::$userClient);
        $accountForm = $needFillAccountPageCrawler->selectButton('Сохранить')->form();
        self::$userClient->submit($accountForm, [
            'student[firstName]' => 'my_first_name',
            'student[fatherName]' => 'my_father_name',
            'student[lastName]' => 'my_last_name',
        ]);

        $this->assertRedirectionLocationMatch('#/teacher/\d+/appoint/#', self::$userClient);
        self::$userClient->followRedirect();
        $this->assertTrue(self::$userClient->getResponse()->isRedirect('/account/'));

        return self::$userClient->followRedirect();
    }

    /**
     * @test
     * @depends  userFillsAccount
     */
    public function userSeesAppointedTeacher(Crawler $accountIndexPageCrawler): void
    {
        $this->assertResponseIsSuccessful(self::$userClient);
        $this->assertNotEmpty($this->getTeacherInfoCrawler($accountIndexPageCrawler));
    }

    /**
     * @test
     * @depends  userFillsAccount
     */
    public function userDisappointsTeacher(): Crawler
    {
        $teacherIndexPageCrawler = $this->userEntersToTeacherIndexPage();
        $disappointTeacherLinkCrawler = $this->getFirstTeacherCrawler($teacherIndexPageCrawler)->selectLink('Удалить');
        $this->assertNotEmpty($disappointTeacherLinkCrawler);
        self::$userClient->click($disappointTeacherLinkCrawler->link());
        $this->assertTrue(self::$userClient->getResponse()->isRedirect('/account/'));

        return self::$userClient->followRedirect();
    }

    /**
     * @test
     * @depends  userDisappointsTeacher
     */
    public function UserDoesNotSeeDisappointedTeacher(Crawler $accountIndexPageCrawler): void
    {
        $this->assertResponseIsSuccessful(self::$userClient);
        $this->assertEmpty($this->getTeacherInfoCrawler($accountIndexPageCrawler));
    }

    private function getFirstTeacherCrawler(Crawler $teacherIndexPageCrawler): Crawler
    {
        return $teacherIndexPageCrawler->filter('tbody tr:first-child');
    }

    private function getTeacherInfoCrawler(Crawler $accountIndexPageCrawler): Crawler
    {
        return $accountIndexPageCrawler->filter('#account-teacher');
    }
}
