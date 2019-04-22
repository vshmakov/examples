<?php

namespace App\Tests\Functional\Security;

use App\Tests\Functional\BaseWebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;

final class UserRegistersOnSiteTest extends BaseWebTestCase
{
    use SecurityAssertsTrait;

    private const  USERNAME = 'some_user';
    private const  PASSWORD = '123';

    /** @var Client */
    private static $userClient;

    public static function setUpBeforeClass(): void
    {
        self::$userClient = self::createClient();
    }

    /**
     * @test
     */
    public function userEntersToRegisterPage(): Crawler
    {
        $registerPageCrawler = self::$userClient->request(Request::METHOD_GET, '/register/');
        $this->assertResponseIsSuccessful(self::$userClient);

        return $registerPageCrawler;
    }

    /**
     * @test
     * @depends  userEntersToRegisterPage
     */
    public function userRegisters(Crawler $registerPageCrawler): Mailer
    {
        $registerForm = $registerPageCrawler->selectButton('Зарегистрироваться')->form();
        self::$userClient->submit($registerForm, [
            'fos_user_registration_form[username]' => self::USERNAME,
            'fos_user_registration_form[email]' => 'abc@def.com',
            'fos_user_registration_form[plainPassword][first]' => self::PASSWORD,
            'fos_user_registration_form[plainPassword][second]' => self::PASSWORD,
        ]);

        $this->assertTrue(self::$userClient->getResponse()->isRedirect('/register/check-email'));

        return self::$userClient->getContainer()->get('fos_user.mailer');
    }

    /**
     * @test
     * @depends  userRegisters
     */
    public function userSeesRequiredConfirmationPage(): void
    {
        self::$userClient->followRedirect();
        $this->assertResponseIsSuccessful(self::$userClient);
    }

    /**
     * @test
     * @depends  userRegisters
     * @depends  userSeesRequiredConfirmationPage
     */
    public function userGetsConfirmationEmail(Mailer $mailer): Crawler
    {
        $confirmationEmailCrawler = new Crawler($mailer->getMessages()->last());
        $confirmationLinkCrawler = $confirmationEmailCrawler->selectLink('ссылке');
        $this->assertNotEmpty($confirmationLinkCrawler);

        return $confirmationLinkCrawler;
    }

    /**
     * @test
     * @depends  userGetsConfirmationEmail
     */
    public function userConfirmsRegistration(Crawler $confirmationLinkCrawler): Crawler
    {
        self::$userClient->click($confirmationLinkCrawler->link());
        $this->assertTrue(self::$userClient->getResponse()->isRedirect('/register/confirmed'));
        self::$userClient->followRedirect();

        return $this->assertSignedIn(self::$userClient);
    }

    /**
     * @test
     * @depends  userConfirmsRegistration
     */
    public function userLogout(Crawler $homepageCrawler): Crawler
    {
        $logoutLinkCrawler = $homepageCrawler->selectLink('Выйти');
        $this->assertNotEmpty($logoutLinkCrawler);
        self::$userClient->click($logoutLinkCrawler->link());

        $this->assertRedirectionToHomepage(self::$userClient);
        $homepageCrawler = self::$userClient->followRedirect();
        $loginLinkCrawler = $homepageCrawler->selectLink('Войти');
        $this->assertNotEmpty($loginLinkCrawler);

        return $loginLinkCrawler;
    }

    /**
     * @test
     * @depends  userLogout
     */
    public function userEntersToLoginPage(Crawler $loginLinkCrawler): void
    {
        $loginPageCrawler = self::$userClient->click($loginLinkCrawler->link());
        $this->assertResponseIsSuccessful(self::$userClient);
        $loginForm = $loginPageCrawler->selectButton('Войти')->form();
        self::$userClient->submit($loginForm, [
            '_username' => self::USERNAME,
            '_password' => self::PASSWORD,
        ]);
        $this->assertSignedIn(self::$userClient);
    }
}
