<?php

declare(strict_types=1);

namespace App\Tests\Functional\Attempt;

use App\DataFixtures\Attempt\ProfileFixtures;
use Symfony\Component\HttpKernel\Client;

final class SolveAttemptTest extends AbstractSolveAttemptTest
{
    /** @var Client */
    private static $unauthenticatedClient;

    public static function setupBeforeClass(): void
    {
        self::$unauthenticatedClient = self::createClient();
    }

    /**
     * @test
     */
    public function guestStartsNewAttempt(): int
    {
        self::$unauthenticatedClient->request('GET', '/attempt/new/');

        return $this->getAttemptId(self::$unauthenticatedClient);
    }

    /**
     * @test
     * @depends guestStartsNewAttempt
     */
    public function guestGetsAttemptData(int $attemptId): array
    {
        $attemptData = $this->getAttemptData($attemptId, self::$unauthenticatedClient);

        $this->assertSame(ProfileFixtures::GUEST_PROFILE_DESCRIPTION, $attemptData['settings']['description']);
        $this->assertSame(ProfileFixtures::GUEST_PROFILE['examplesCount'], $attemptData['result']['remainedExamplesCount']);
        $this->assertContains('isFinished', $attemptData['result']);

        return $attemptData;
    }

    /**
     * @testt
     * @depends guestGetsAttemptData
     */
    public function guestSolvesAttempt(array $attemptData): void
    {
        $this->solveAttempt($attemptData, self::$unauthenticatedClient);
    }

    /**
     * @test
     * @depends  guestStartsNewAttempt
     * @depends  guestSolvesAttempt
     */
    public function guestRedirectsToAttemptResult(int $attemptId): void
    {
        self::$unauthenticatedClient->request('GET', sprintf('/attempt/%s/', $attemptId));
        $this->assertTrue(self::$unauthenticatedClient->getResponse()->isRedirection());
        $this->assertTrue(self::$unauthenticatedClient->getResponse()->isRedirect(sprintf('/attempt/%s/show/', $attemptId)));
    }

    /**
     * @test
     * @depends  guestStartsNewAttempt
     */
    public function guestShowAttemptResult(int $attemptId): void
    {
        self::$unauthenticatedClient->request('GET', sprintf('/attempt/%s/show/', $attemptId));
        $this->assertTrue(self::$unauthenticatedClient->getResponse()->isSuccessful());
    }

    /**
     * @test
     * @depends  guestStartsNewAttempt
     */
    public function guestShowAttemptSettings(int $attemptId): void
    {
        self::$unauthenticatedClient->request('GET', sprintf('/attempt/%s/settings/', $attemptId));
        $this->assertTrue(self::$unauthenticatedClient->getResponse()->isSuccessful());
    }
}
