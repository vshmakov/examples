<?php

declare(strict_types=1);

namespace App\Tests\Functional\Attempt;

use App\Tests\Functional\BaseWebTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

abstract class AbstractSolveAttemptTest extends BaseWebTestCase
{
    protected function getAttemptId(Client $client): int
    {
        $matches = $this->assertRedirectionLocationMatch('#/attempt/(?<attemptId>\d+)/$#', $client);

        return (int) $matches['attemptId'];
    }

    protected function getAttemptData(int $attemptId, Client $client): array
    {
        $attemptData = self::ajaxGet($client, sprintf('/api/attempts/%s.json', $attemptId));
        $this->assertTrue($client->getResponse()->isSuccessful());

        return $attemptData;
    }

    protected function solveAttempt(array $attemptData, Client $client): void
    {
        $solvedExamplesCount = 0;

        while (1 <= $attemptData['result']['remainedExamplesCount']) {
            $this->assertLessThan(10, $solvedExamplesCount++);
            $answerCallback = 1 !== $solvedExamplesCount ? 'rightAnswer' : 'wrongAnswer';
            $answerAttemptData = \call_user_func([$this, $answerCallback], $attemptData['example']['string'], $attemptData['id'], $client);
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

    private function rightAnswer(string $example, int $attemptId, Client $client): array
    {
        $answerData = $this->answer($this->solve($example), $attemptId, $client);
        $this->assertTrue($answerData['isRight']);

        return $answerData;
    }

    private function wrongAnswer(string $example, int $attemptId, Client $client): array
    {
        $answerData = $this->answer($this->solve($example) + 1, $attemptId, $client);
        $this->assertFalse($answerData['isRight']);

        return $answerData;
    }

    private function answer(int $answer, int $attemptId, Client $client): array
    {
        $answerData = self::ajaxPut(
            $client,
            sprintf('/api/attempts/%s/answer.json', $attemptId),
            ['answer' => $answer,
            ]);
        $this->assertTrue($client->getResponse()->isSuccessful());

        return $answerData;
    }

    private function solve(string $example): float
    {
        $expressionLanguage = new ExpressionLanguage();
        $example = str_replace(':', '/', $example);

        return $expressionLanguage->evaluate($example);
    }
}
