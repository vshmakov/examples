<?php

namespace App\Tests\Functional\Attempt;

use App\DataFixtures\UserFixtures;
use App\Tests\Functional\BaseWebTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\HttpFoundation\Response;

final class GetAttemptsCollectionTest extends BaseWebTestCase
{
    private const  GET_USER_ATTEMPTS_COLLECTION_ROUTE = '/api/attempts.jsondt';

    /** @var Client */
    private static $unauthenticatedClient;

    public static function setUpBeforeClass(): void
    {
        self::$unauthenticatedClient = self::createClient();
    }

    /**
     * @test
     */
    public function guestGetsJsondtAttemptsCollection(): void
    {
        self::ajaxGet(self::$unauthenticatedClient, self::GET_USER_ATTEMPTS_COLLECTION_ROUTE, ['draw' => 1, 'username' => UserFixtures::GUEST_USERNAME]);
        $this->assertResponseIsSuccessful(self::$unauthenticatedClient);
    }

    /**
     * @test
     * @depends  guestGetsJsondtAttemptsCollection
     */
    public function guestGetsBadRequestHttpExceptionWithOutSendingDraw(): void
    {
        self::ajaxGet(self::$unauthenticatedClient, self::GET_USER_ATTEMPTS_COLLECTION_ROUTE);
        $this->assertSame(Response::HTTP_BAD_REQUEST, self::$unauthenticatedClient->getResponse()->getStatusCode());
    }
}
