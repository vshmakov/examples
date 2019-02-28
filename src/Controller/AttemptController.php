<?php

namespace App\Controller;

use App\Entity\Attempt;
use App\Form\SettingsType;
use App\Repository\AttemptRepository;
use App\Repository\ExampleRepository;
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
    use BaseTrait;

    /**
     * @Route("/", name="attempt_index")
     */
    public function index(AttemptRepository $attemptRepository): Response
    {
        return $this->render('attempt/index.html.twig', [
            'attempts' => $attemptRepository->findAllByCurrentUser(),
        ]);
    }

    /**
     * @Route("/{id}/show", name="attempt_show", requirements={"id": "\d+"})
     * @IsGranted(AttemptVoter::VIEW, subject="attempt")
     */
    public function show(Attempt $attempt, ExampleRepository $exampleRepository, AttemptRepository $attemptRepository): Response
    {
        return $this->render('attempt/show.html.twig', [
            'attemptRepository' => $attemptRepository,
            'att' => $attempt->setEntityRepository($attemptRepository),
            'examples' => $exampleRepository->findByAttempt($attempt),
            'exR' => $exampleRepository,
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
    public function new(AttemptRepository $attemptRepository): RedirectResponse
    {
        return $this->redirectToRoute('attempt_solve', [
            'id' => $attemptRepository->getNewByCurrentUser()->getId(),
        ]);
    }
}
