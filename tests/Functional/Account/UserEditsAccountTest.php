<?php

declare(strict_types=1);

namespace App\Tests\Functional\Account;

use App\DataFixtures\UserFixtures;
use App\Request\Http\Method;
use App\Tests\Functional\BaseWebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use  Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

final class UserEditsAccountTest extends BaseWebTestCase
{
    private const  TEMPORARY_USERNAME = 'my_temporary_username';

    /** @var Client */
    private static $userClient;

    public static function setUpBeforeClass(): void
    {
        self::$userClient = self::createAuthenticatedClient(UserFixtures::SIMPLE_USER_USERNAME);
    }

    /**
     * @test
     */
    public function userEntersToAccountIndexPage(): Crawler
    {
        $accountIndexPageCrawler = self::$userClient->request(Method::GET, '/account/');
        $this->assertResponseIsSuccessful(self::$userClient);

        return $accountIndexPageCrawler;
    }

    /**
     * @test
     * @depends userEntersToAccountIndexPage
     */
    public function userEntersToEditAccountPage(Crawler $accountIndexPageCrawler): Crawler
    {
        $editAccountPageCrawler = self::$userClient->click($accountIndexPageCrawler->selectLink('Редактировать профиль')->link());
        $this->assertResponseIsSuccessful(self::$userClient);

        return $editAccountPageCrawler;
    }

    /**
     * @test
     * @depends  userEntersToEditAccountPage
     */
    public function userCanNotChooseBusyUsername(Crawler $editAccountPageCrawler): Form
    {
        $editAccountForm = $this->getEditAccountForm($editAccountPageCrawler);
        $editAccountPageCrawler = self::$userClient->submit($editAccountForm, [
            'account[firstName]' => 'Ivan',
            'account[fatherName]' => 'Ivanovich',
            'account[lastName]' => 'Ivanov',
            'account[username]' => UserFixtures::STUDENT_USERNAME,
        ]);
        $this->assertFalse(self::$userClient->getResponse()->isRedirection());
        $usernameFirstErrorCrawler = $editAccountPageCrawler->filter('.account-username-input li:first-child');
        $this->assertSame('Логин занят', $usernameFirstErrorCrawler->text());

        return $editAccountForm;
    }

    /**
     * @test
     * @depends  userCanNotChooseBusyUsername
     */
    public function userEditsAccount(Form $editAccountForm): void
    {
        self::$userClient->submit($editAccountForm, [
            'account[username]' => self::TEMPORARY_USERNAME,
        ]);
        $this->assertRedirectionToAccountIndexPage(self::$userClient);
    }

    /**
     * @test
     * @depends userEditsAccount
     */
    public function userMovesBackOriginalUsername(): void
    {
        $newUsernameClient = self::createAuthenticatedClient(self::TEMPORARY_USERNAME);
        $editAccountPageCrawler = $newUsernameClient->request(Method::GET, '/account/edit/');
        $editAccountForm = $this->getEditAccountForm($editAccountPageCrawler);
        $newUsernameClient->submit($editAccountForm, [
            'account[username]' => UserFixtures::SIMPLE_USER_USERNAME,
        ]);
        $this->assertRedirectionToAccountIndexPage($newUsernameClient);
        $this->userEntersToAccountIndexPage();
    }

    private function getEditAccountForm(Crawler $editAccountPageCrawler): Form
    {
        return $editAccountPageCrawler->selectButton('Сохранить')->form();
    }

    private function assertRedirectionToAccountIndexPage(Client $client): void
    {
        $this->assertTrue($client->getResponse()->isRedirect('/account/'));
    }
}
