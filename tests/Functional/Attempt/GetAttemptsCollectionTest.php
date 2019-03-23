<?php

namespace App\Tests\Functional\Attempt;

use App\ApiPlatform\Format;
use App\Tests\Functional\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;

final class GetAttemptsCollectionTest extends BaseWebTestCase
{
    private const  GET_USER_ATTEMPTS_COLLECTION_ROUTE = '/api/users/me/attempts.%s';

    /** @var Client */
    private static $unauthenticatedClient;

    public static function setUpBeforeClass(): void
    {
        self::$unauthenticatedClient = self::createClient();
    }

    /**
     * @test
     */
    public function guestGetsJsonAttemptsCollection(): void
    {
        self::ajaxGet(self::$unauthenticatedClient, sprintf(self::GET_USER_ATTEMPTS_COLLECTION_ROUTE, Format::JSON));
        $this->assertTrue(self::$unauthenticatedClient->getResponse()->isSuccessful());
    }

    /**
     * @test
     */
    public function guestGetsBadRequestHttpExceptionWithOutSendingDraw(): void
    {
        self::ajaxGet(self::$unauthenticatedClient, sprintf(self::GET_USER_ATTEMPTS_COLLECTION_ROUTE, Format::JSONDT));
        $this->assertSame(Response::HTTP_BAD_REQUEST, self::$unauthenticatedClient->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function guestGetsJsondtAttemptsCollection(): void
    {
        $draw = 12345;
        $selectAttemptsCount = 3;
        $dataTablesResponse = self::ajaxGet(self::$unauthenticatedClient, sprintf(self::GET_USER_ATTEMPTS_COLLECTION_ROUTE, Format::JSONDT), ['draw' => $draw, 'start' => 0, 'length' => $selectAttemptsCount]);

        $this->assertTrue(self::$unauthenticatedClient->getResponse()->isSuccessful());
        $this->assertSame($draw, $dataTablesResponse['draw']);
        $this->assertGreaterThan($selectAttemptsCount, $dataTablesResponse['recordsTotal'], "Database must contains greater than $selectAttemptsCount attempts");
        $this->assertSame($selectAttemptsCount, \count($dataTablesResponse['data']));
    }
}
