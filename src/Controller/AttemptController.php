<?php

namespace App\Controller;

use App\ApiPlatform\Attribute;
use App\Attempt\AttemptCreatorInterface;
use App\Attempt\Example\ExampleResponseProviderInterface;
use App\Entity\Attempt;
use App\Form\SettingsType;
use App\Iterator;
use App\Parameter\Api\Format;
use App\Repository\AttemptRepository;
use App\Repository\ProfileRepository;
use App\Response\AttemptResponseProviderInterface;
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
     * @Route("/{id}/show", name="attempt_show", requirements={"id": "\d+"})
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
     * @IsGranted(AttemptVoter::VIEW, subject="attempt")
     */
    public function solve(Attempt $attempt, AttemptResponseProviderInterface $attemptResponseProvider): Response
    {
        $attemptResponse = $attemptResponseProvider->createAttemptResponse($attempt);

        if ($attemptResponse->isFinished()) {
            return $this->redirectToRoute('attempt_show', ['id' => $attempt->getId()]);
        }

        return $this->render('attempt/solve.html.twig', [
            'jsParams' => [
                'solveAttemptDataUrl' => $this->generateUrl('api_attempt_solve_data', ['id' => $attempt->getId()]),
                'answerAttemptUrl' => $this->generateUrl('api_attempt_answer', ['id' => $attempt->getId()]),
                'showAttemptUrl' => $this->generateUrl('attempt_show', ['id' => $attempt->getId()]),
            ],
            'attempt' => $attempt,
            'attemptResponse' => $attemptResponse,
        ]);
    }

    /**
     * @Route("/{id}/profile", name="attempt_profile")
     * @IsGranted(AttemptVoter::VIEW, subject="attempt")
     */
    public function profile(Attempt $attempt, ProfileRepository $profileRepository, AttemptRepository $attemptRepository): Response
    {
        $profile = $attempt->getSettings()->setEntityRepository($profileRepository);

        return $this->render('attempt/profile.html.twig', [
            'jsParams' => [
                'canEdit' => false,
            ],
            'profile' => $profile,
            'form' => $this->createForm(SettingsType::class, $profile)->createView(),
            'att' => $attempt->setEntityRepository($attemptRepository),
        ]);
    }

    /**
     * @Route("/last", name="attempt_last")
     *
     * @todo  functional test
     */
    public function last(AttemptRepository $attemptRepository): Response
    {
        if ($attempt = $attemptRepository->findLastActualByCurrentUser()) {
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
