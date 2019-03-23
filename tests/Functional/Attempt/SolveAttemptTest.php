<?php

namespace App\Tests\Functional\Attempt;

use App\DataFixtures\Attempt\ProfileFixtures;
use App\Tests\Functional\BaseWebTestCase;
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
        self::$unauthenticatedClient->request('GET', '/attempt/new/');
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
        self::$attemptData = self::ajaxGet(self::$unauthenticatedClient, sprintf('/api/attempts/%s.json', self::$attemptId));
        $this->assertTrue(self::$unauthenticatedClient->getResponse()->isSuccessful());

        $this->assertSame(ProfileFixtures::GUEST_PROFILE_DESCRIPTION, self::$attemptData['settings']['description']);
        $this->assertSame(ProfileFixtures::GUEST_PROFILE['examplesCount'], self::$attemptData['result']['remainedExamplesCount']);
        $this->assertContains('isFinished', self::$attemptData['result']);
    }

    /**
     * @testt
     * @depends guestGetsAttemptData
     */
    public function guestSolvesAttempt(): void
    {
        $attemptData = self::$attemptData;
        $solvedExamplesCount = 0;

        while (1 <= $attemptData['result']['remainedExamplesCount']) {
            $this->assertLessThan(10, $solvedExamplesCount++);
            $answerCallback = 1 !== $solvedExamplesCount ? 'rightAnswer' : 'wrongAnswer';
            $answerAttemptData = \call_user_func([$this, $answerCallback], $attemptData['example']['string']);
            $attemptData = $answerAttemptData['attempt'];
            $isLastExample = 0 === $attemptData['result']['remainedExamplesCount'];

            if (!$isLastExample) {
                $this->assertFalse($attemptData['result']['isFinished']);
            } else {
                $this->assertTrue($attemptData['result']['isFinished']);
                $this->assertNull($attemptData['example']);
            }
        }
    }

    /**
     * @test
     * @depends  guestSolvesAttempt
     */
    public function guestRedirectsToAttemptResult(): void
    {
        self::$unauthenticatedClient->request('GET', sprintf('/attempt/%s/', self::$attemptId));
        $this->assertTrue(self::$unauthenticatedClient->getResponse()->isRedirection());
        $this->assertTrue(self::$unauthenticatedClient->getResponse()->isRedirect(sprintf('/attempt/%s/show/', self::$attemptId)));
    }

    /**
     * @test
     * @depends  guestSolvesAttempt
     */
    public function guestShowAttemptResult(): void
    {
        self::$unauthenticatedClient->request('GET', sprintf('/attempt/%s/show/', self::$attemptId));
        $this->assertTrue(self::$unauthenticatedClient->getResponse()->isSuccessful());
    }

    /**
     * @test
     * @depends  guestSolvesAttempt
     */
    public function guestShowAttemptSettings(): void
    {
        self::$unauthenticatedClient->request('GET', sprintf('/attempt/%s/settings/', self::$attemptId));
        $this->assertTrue(self::$unauthenticatedClient->getResponse()->isSuccessful());
    }

    private function rightAnswer(string $example): array
    {
        $answerData = $this->answer($this->solve($example));
        $this->assertTrue($answerData['isRight']);

        return $answerData;
    }

    private function wrongAnswer(string $example): array
    {
        $answerData = $this->answer($this->solve($example) + 1);
        $this->assertFalse($answerData['isRight']);

        return $answerData;
    }

    private function answer(int $answer): array
    {
        $answerData = self::ajaxPut(
            self::$unauthenticatedClient,
            sprintf('/api/attempts/%s/answer.json', self::$attemptId),
            ['answer' => $answer,
            ]);
        $this->assertTrue(self::$unauthenticatedClient->getResponse()->isSuccessful());

        return $answerData;
    }

    private function solve(string $example): float
    {
        $expressionLanguage = new ExpressionLanguage();
        $example = str_replace(':', '/', $example);

        return $expressionLanguage->evaluate($example);
    }
}
