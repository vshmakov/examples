<?php

namespace App\Tests\Functional;

use App\DataFixtures\UserFixtures;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

final class PagesAreAvailableTest extends BaseWebTestCase
{
    private const FREE_ENDPOINTS = [
        '/',
        '/attempt/',
        '/profile/',
        '/login',
        '/resetting/request',
        '/register/',
    ];

    private const ALL_AUTHENTICATED_USERS_ENDPOINTS = [
        '/profile/new/',
        '/account/',
        '/account/edit/',
    ];

    private const ADMIN_ENDPOINTS = [
        '/admin/',
        //'/admin/user/',
        //'/admin/session/',
    ];

    private const STUDENT_ENDPOINTS = [
        '/teacher/',
    ];

    private const TEACHER_ENDPOINTS = [
        '/student/',
        '/task/',
        '/task/new/',
    ];

    /** @var Client */
    private static $unauthenticatedClient;

    /** @var Client */
    private static $adminAuthenticatedClient;

    /** @var Client */
    private static $studentAuthenticatedClient;

    /** @var Client */
    private static $teacherAuthenticatedClient;

    public static function setUpBeforeClass(): void
    {
        self::$unauthenticatedClient = static::createClient();
        self::$adminAuthenticatedClient = static::createAuthenticatedClient(UserFixtures::ADMIN_USERNAME);
        self::$studentAuthenticatedClient = self::createAuthenticatedClient(UserFixtures::STUDENT_USERNAME);
        self::$teacherAuthenticatedClient = static::createAuthenticatedClient(UserFixtures::TEACHER_USERNAME);
    }

    public function freeEndpointsProvider(): array
    {
        return $this->wrapItemsInArray(self::FREE_ENDPOINTS);
    }

    public function authenticatedUserEndpointsProvider(): array
    {
        return $this->wrapItemsInArray(self::ALL_AUTHENTICATED_USERS_ENDPOINTS);
    }

    public function adminAvailableEndpointsProvider(): array
    {
        return $this->wrapItemsInArray(self::ADMIN_ENDPOINTS);
    }

    public function studentAvailableEndpointsProvider(): array
    {
        return $this->wrapItemsInArray(self::STUDENT_ENDPOINTS);
    }

    public function teacherAvailableEndpointsProvider(): array
    {
        return $this->wrapItemsInArray(self::TEACHER_ENDPOINTS);
    }

    /**
     * @test
     * @dataProvider freeEndpointsProvider
     */
    public function guestEntersToFreeEndpoints(string $url): void
    {
        self::$unauthenticatedClient->request('GET', $url);
        $this->assertTrue(self::$unauthenticatedClient->getResponse()->isSuccessful());
    }

    /**
     * @test
     * @dataProvider adminAvailableEndpointsProvider
     * @dataProvider authenticatedUserEndpointsProvider
     * @dataProvider studentAvailableEndpointsProvider
     * @dataProvider teacherAvailableEndpointsProvider
     */
    public function unauthenticatedUserRedirectsToLoginPage(string $url): void
    {
        self::$unauthenticatedClient->request('GET', $url);
        $this->assertRedirectionToLoginPage(self::$unauthenticatedClient);
    }

    /**
     * @test
     * @dataProvider adminAvailableEndpointsProvider
     * @dataProvider authenticatedUserEndpointsProvider
     * @dataProvider studentAvailableEndpointsProvider
     */
    public function adminEntersToAllEndpoints(string $url): void
    {
        self::$adminAuthenticatedClient->request('GET', $url);
        $this->assertTrue(self::$adminAuthenticatedClient->getResponse()->isSuccessful());
    }

    /**
     * @test
     * @dataProvider authenticatedUserEndpointsProvider
     * @dataProvider studentAvailableEndpointsProvider
     */
    public function studentEntersToAvailableEndpoints(string $url): void
    {
        self::$studentAuthenticatedClient->request('GET', $url);
        $this->assertTrue(self::$studentAuthenticatedClient->getResponse()->isSuccessful());
    }

    /**
     * @test
     * @dataProvider adminAvailableEndpointsProvider
     * @dataProvider teacherAvailableEndpointsProvider
     */
    public function studentDasNotAbleToAccess(string $url): void
    {
        self::$studentAuthenticatedClient->request('GET', $url);
        $this->assertSame(Response::HTTP_FORBIDDEN, self::$studentAuthenticatedClient->getResponse()->getStatusCode());
    }

    /**
     * @test
     * @dataProvider authenticatedUserEndpointsProvider
     * @dataProvider teacherAvailableEndpointsProvider
     */
    public function teacherEntersToAvailableEndpoints(string $url): void
    {
        self::$teacherAuthenticatedClient->request('GET', $url);
        $this->assertTrue(self::$teacherAuthenticatedClient->getResponse()->isSuccessful());
    }

    /**
     * @test
     * @dataProvider adminAvailableEndpointsProvider
     * @dataProvider studentAvailableEndpointsProvider
     */
    public function teacherDasNotAbleToAccess(string $url): void
    {
        self::$teacherAuthenticatedClient->request('GET', $url);
        $this->assertSame(Response::HTTP_FORBIDDEN, self::$teacherAuthenticatedClient->getResponse()->getStatusCode());
    }
}
