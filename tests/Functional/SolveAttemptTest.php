<?php

namespace App\Tests\Functional;

use App\DataFixtures\Attempt\ProfileFixtures;
use Symfony\Component\HttpKernel\Client;

final class SolveAttemptTest extends BaseWebTestCase
{
    /** @var Client */
    private static $unauthenticatedClient;

    /** @var int */
    private static $attemptId;

    /** @var array */
    private static $attemptData;

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
    public function guestGetsAttemptData(): void
    {
        self::$attemptData = self::ajaxGet(self::$unauthenticatedClient, sprintf('/api/attempt/%s/solve-data/', self::$attemptId));
        $this->assertTrue(self::$unauthenticatedClient->getResponse()->isSuccessful());

        $this->assertSame(ProfileFixtures::GUEST_PROFILE_DESCRIPTION, self::$attemptData['settings']['description']);
        $this->assertSame(ProfileFixtures::GUEST_PROFILE['examplesCount'], self::$attemptData['remainedExamplesCount']);
        $this->assertContains('isFinished', self::$attemptData);
    }

    /**
     * @testt
     * @depends guestGetsAttemptData
     */
    public function guestSolvesAttempt(): void
    {
    }
}
