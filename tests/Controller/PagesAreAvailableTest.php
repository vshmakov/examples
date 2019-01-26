<?php

namespace App\Tests\Controller;

use App\DataFixtures\UserFixtures;
use Symfony\Bundle\FrameworkBundle\Client;

class PagesAreAvailableTest extends BaseWebTestCase
{
    /** @var Client */
    private static $unauthenticatedClient;

    /** @var Client */
    private static $studentAuthenticatedClient;

    public static function setUpBeforeClass(): void
    {
        self::$unauthenticatedClient = static::createClient();
        self::$studentAuthenticatedClient = self::createAuthenticatedClient(UserFixtures::STUDENT_USERNAME);
    }

    public function freeEndpointsProvider(): array
    {
        return $this->wrapItemsInArray([
            '/',
            '/attempt/',
            '/profile/',
            '/login',
            '/register/',
        ]);
    }

    /**
     * @test
     * @dataProvider freeEndpointsProvider
     */
    public function freeEndpointsReturnsHttpOkStatus(string $url): void
    {
        self::$unauthenticatedClient->request('GET', $url);
        $this->assertTrue(self::$unauthenticatedClient->getResponse()->isSuccessful());
    }

    public function studentAvailableEndpointsProvider(): array
    {
        return $this->wrapItemsInArray([
            '/account/',
            '/teacher/',
            '/homework/',
        ]);
    }

    public function teacherAvailableEndpointsProvider(): array
    {
        return $this->wrapItemsInArray([
    '/student/',
    '/task/',
]);
    }

    /**
     * @test
     * @dataProvider studentAvailableEndpointsProvider
     * @dataProvider teacherAvailableEndpointsProvider
     */
    public function unauthenticatedUserRedirectsToLoginPage(string $url): void
    {
        self::$unauthenticatedClient->request('GET', $url);
        $response = self::$unauthenticatedClient->getResponse();

        $this->assertTrue($response->isRedirection());
        $this->assertRegExp('#/login$#', $response->headers->get('location'));
    }

    /**
     * @test
     * @dataProvider studentAvailableEndpointsProvider
     */
    public function studentEntersToAvailableEndpoints(string $url): void
    {
        self::$studentAuthenticatedClient->request('GET', $url);
        $this->assertTrue(self::$studentAuthenticatedClient->getResponse()->isSuccessful());
    }
}
