<?php

namespace App\Controller\Api;

use App\Attempt\AttemptResponseProviderInterface;
use App\Attempt\AttemptResultProviderInterface;
use App\Entity\Attempt;
use App\Response\AttemptResponse;
use App\Security\Voter\AttemptVoter;
use App\Serializer\Group;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/attempt")
 */
final class AttemptController extends BaseController
{
    public function answer(Attempt $data, Request $request, AttemptResponseProviderInterface $attemptResponseProvider, AttemptResultProviderInterface $attemptResultProvider): array
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
        $this->getDoctrine()
            ->getManager()
            ->flush();
        $attemptResultProvider->updateAttemptResult($data);

        return $createAnswerAttemptResponseData(
            $example->isRight(),
            $attemptResponseProvider->createAttemptResponse($data)
        );
    }

    /**
     * @Route("/{id}/solve-data/", name="api_attempt_solve_data")
     * @IsGranted(AttemptVoter::VIEW, subject="attempt")
     */
    public function solveData(Attempt $attempt, AttemptResponseProviderInterface $attemptResponseProvider): array
    {
        return $this->normalize(
            $attemptResponseProvider->createAttemptResponse($attempt)
        );
    }

    /**
     * @param mixed $data
     */
    private function normalize($data): array
    {
        return $this->getNormalizer()
            ->normalize($data, null, ['groups' => Group::ATTEMPT]);
    }
}
