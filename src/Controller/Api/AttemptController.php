<?php

namespace App\Controller\Api;

use App\Attempt\AttemptResponseFactoryInterface;
use App\Attempt\AttemptResultProviderInterface;
use App\Attempt\Example\ExampleSolverInterface;
use App\Entity\Attempt;
use App\Response\AttemptResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

final class AttemptController
{
    public function answer(Attempt $data, Request $request, AttemptResponseFactoryInterface $attemptResponseProvider, AttemptResultProviderInterface $attemptResultProvider, EntityManagerInterface $entityManager, ExampleSolverInterface $exampleSolver): array
    {
        $createAnswerAttemptResponseData = function (?bool $isRight, AttemptResponse $attemptResponse): array {
            return [
                'isRight' => $isRight,
                'attempt' => $attemptResponse,
            ];
        };
        $attemptResponse = $attemptResponseProvider->createAttemptResponse($data);

        if ($data->getResult()->isFinished()) {
            return $createAnswerAttemptResponseData(null, $attemptResponse);
        }

        $example = $attemptResponse->getExample()->getExample();

        $answer = (float) $request->request->get('answer');
        $example->setAnswer($answer);
        $example->setIsRight($exampleSolver->isRight($answer, $example));
        $entityManager->flush();
        $attemptResultProvider->updateAttemptResult($data);

        return $createAnswerAttemptResponseData(
            $example->isRight(),
            $attemptResponseProvider->createAttemptResponse($data)
        );
    }
}
