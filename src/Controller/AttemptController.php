<?php

namespace App\Controller;

use App\ApiPlatform\Attribute;
use App\ApiPlatform\Format;
use App\Attempt\AttemptCreatorInterface;
use App\Attempt\AttemptProviderInterface;
use App\Attempt\AttemptResponseProviderInterface;
use App\Attempt\Example\ExampleResponseProviderInterface;
use App\Controller\Traits\CurrentUserProviderTrait;
use App\Controller\Traits\JavascriptParametersTrait;
use App\Entity\Attempt;
use App\Iterator;
use App\Security\Voter\AttemptVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/attempt")
 */
final class AttemptController extends Controller
{
    use CurrentUserProviderTrait, JavascriptParametersTrait;

    /**
     * @Route("/", name="attempt_index")
     */
    public function index(): Response
    {
        $this->setJavascriptParameters([
            'getAttemptsUrl' => $this->generateUrl('api_attempts_get_user_attempts_collection', [Attribute::FORMAT => Format::JSONDT]),
        ]);

        return $this->render('attempt/index.html.twig');
    }

    /**
     * @Route("/{id}/show/", name="attempt_show", requirements={"id": "\d+"})
     * @IsGranted(AttemptVoter::VIEW, subject="attempt")
     */
    public function show(Attempt $attempt, AttemptResponseProviderInterface $attemptResponseProvider, ExampleResponseProviderInterface $exampleResponseProvider): Response
    {
        return $this->render('attempt/show.html.twig', [
            'attempt' => $attempt,
            'attemptResponse' => $attemptResponseProvider->createAttemptResponse($attempt),
            'exampleResponses' => Iterator::map($attempt->getExamples(), [$exampleResponseProvider, 'createExampleResponse']),
        ]);
    }

    /**
     * @Route("/{id}/", name="attempt_solve", requirements={"id": "\d+"})
     * @IsGranted(AttemptVoter::SOLVE, subject="attempt")
     */
    public function solve(Attempt $attempt, AttemptResponseProviderInterface $attemptResponseProvider): Response
    {
        if ($attempt->getResult()->isFinished()) {
            return $this->redirectToRoute('attempt_show', ['id' => $attempt->getId()]);
        }

        $this->setJavascriptParameters([
            'solveAttemptDataUrl' => $this->generateUrl('api_attempts_get_item', ['id' => $attempt->getId(), Attribute::FORMAT => Format::JSON]),
            'answerAttemptUrl' => $this->generateUrl('api_attempts_answer_item', ['id' => $attempt->getId(), Attribute::FORMAT => Format::JSON]),
            'showAttemptUrl' => $this->generateUrl('attempt_show', ['id' => $attempt->getId()]),
        ]);

        return $this->render('attempt/solve.html.twig', [
            'attempt' => $attempt,
            'attemptResponse' => $attemptResponseProvider->createAttemptResponse($attempt),
        ]);
    }

    /**
     * @Route("/{id}/settings/", name="attempt_settings")
     * @IsGranted(AttemptVoter::VIEW, subject="attempt")
     */
    public function profile(Attempt $attempt, AttemptResponseProviderInterface $attemptResponseProvider): Response
    {
        return $this->render('attempt/settings.html.twig', [
            'attempt' => $attempt,
            'settings' => $attempt->getSettings(),
            'attemptResponse' => $attemptResponseProvider->createAttemptResponse($attempt),
        ]);
    }

    /**
     * @Route("/last/", name="attempt_last")
     */
    public function last(AttemptProviderInterface $attemptProvider): Response
    {
        if ($attempt = $attemptProvider->getLastAttempt()) {
            return $this->redirectToRoute('attempt_solve', ['id' => $attempt->getId()]);
        }

        return $this->redirectToRoute('attempt_index');
    }

    /**
     * @Route("/new", name="attempt_new")
     */
    public function new(AttemptCreatorInterface $creator): RedirectResponse
    {
        return $this->redirectToRoute('attempt_solve', [
            'id' => $creator->createAttempt()->getId(),
        ]);
    }
}
