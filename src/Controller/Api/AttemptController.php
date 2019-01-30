<?php

namespace App\Controller\Api;

use App\Entity\Attempt;
use App\Repository\ExampleRepository;
use App\Response\AttemptResponse;
use App\Response\AttemptResponseProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class AttemptController.
 *
 * @Route("/api/attempt")
 */
final class AttemptController
{
    /**
     * @Route("/{id}/answer", name="api_attempt_answer", methods="POST")
     */
    public function answer(Attempt $attempt, Request $request, ExampleRepository $exampleRepository, EntityManagerInterface $entityManager, AttemptResponseProviderInterface $attemptResponseProvider, NormalizerInterface $normalizer): AttemptResponse
    {
        $attemptResponse = $attemptResponseProvider->createAttemptResponse($attempt);

        if ($attemptResponse->isFinished()) {
            return $attemptResponse;
        }

        $example = $exampleRepository->findLastUnansweredByAttempt($attempt);
        $answer = (float) $request->request->get('answer');
        $example->setAnswer($answer);
        $entityManager->flush();

        return $attemptResponseProvider->createAttemptResponse($attempt);
    }

    /**
     * @Route("/{id}/solve-data/", name="api_attempt_solve_data")
     * @IsGranted("VIEW", subject="attempt")
     */
    public function solveData(Attempt $attempt, AttemptResponseProviderInterface $attemptResponseProvider): AttemptResponse
    {
        return $attemptResponseProvider->createAttemptResponse($attempt);
    }
}
