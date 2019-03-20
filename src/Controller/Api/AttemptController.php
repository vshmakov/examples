<?php

namespace App\Controller\Api;

use App\Attempt\AttemptResponseProviderInterface;
use App\Attempt\AttemptResultProviderInterface;
use App\Entity\Attempt;
use App\Response\AttemptResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

final class AttemptController
{
    public function answer(Attempt $data, Request $request, AttemptResponseProviderInterface $attemptResponseProvider, AttemptResultProviderInterface $attemptResultProvider, EntityManagerInterface $entityManager): array
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
        $entityManager->flush();
        $attemptResultProvider->updateAttemptResult($data);

        return $createAnswerAttemptResponseData(
            $example->isRight(),
            $attemptResponseProvider->createAttemptResponse($data)
        );
    }
}
