<?php

namespace App\Tests\Functional;

use Symfony\Component\HttpKernel\Client;

final class SolveAttemptTest extends BaseWebTestCase
{
    /** @var Client */
    private static $unauthenticatedClient;
    /** @var int */
    private static $attemptId;

    public static function setupBeforeClass(): void
    {
        self::$unauthenticatedClient = self::createClient();
    }

    /**
     * @test
     */
    public function guestStartsNewAttempt(): void
    {
        self::$unauthenticatedClient->request('GET', '/attempt/new');
        $response = self::$unauthenticatedClient->getResponse();
        $this->assertTrue($response->isRedirection());
        $this->assertTrue((bool) preg_match('#/attempt/(?<attemptId>\d+)/$#', $response->headers->get('location'), $matches));
        self::$attemptId = $matches['attemptId'];
    }

    /**
     * @test
     * @depends guestStartsNewAttempt
     */
    public function guestSolvesAttempt(): void
    {
        $attemptData = self::ajaxGet(self::$unauthenticatedClient, sprintf('/api/attempt/%s/solve-data/', self::$attemptId));
        $this->assertTrue(self::$unauthenticatedClient->getResponse()->isSuccessful());
    }
}
