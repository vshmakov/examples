<?php

namespace App\Tests\Functional;

use App\DataFixtures\Attempt\ProfileFixtures;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
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
        $nextExample = self::$attemptData['example']['string'];
        $remainedExamplesCount = self::$attemptData['remainedExamplesCount'];

        while (0 < $remainedExamplesCount) {
            $answerAttemptData = self::ajaxPost(
                self::$unauthenticatedClient,
                sprintf('/api/attempt/%s/answer/', self::$attemptId),
                ['answer' => $this->solve($nextExample)]
            );
            $this->assertTrue(self::$unauthenticatedClient->getResponse()->isSuccessful());
            $answerRemainedExamplesCount = $answerAttemptData['attempt']['remainedExamplesCount'];
            $hasNextExample = 0 < $answerRemainedExamplesCount;

            if ($hasNextExample) {
                $this->assertFalse($answerAttemptData['attempt']['isFinished']);
                $nextExample = $answerAttemptData['attempt']['example']['string'];
            } else {
                $this->assertTrue($answerAttemptData['attempt']['isFinished']);
                $this->assertNull($answerAttemptData['attempt']['example']);
            }

            $this->assertTrue($answerAttemptData['isRight']);
            $this->assertSame($remainedExamplesCount - 1, $answerRemainedExamplesCount);
            $remainedExamplesCount = $answerRemainedExamplesCount;
        }
    }

    private function solve(string $example): float
    {
        $expressionLanguage = new ExpressionLanguage();
        $example = str_replace(':', '/', $example);

        return $expressionLanguage->evaluate($example);
    }
}
