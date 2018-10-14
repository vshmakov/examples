<?php

namespace App\Controller;

use App\Form\ProfileType;
use App\Repository\ProfileRepository;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Attempt;
use App\Repository\AttemptRepository;
use App\Repository\ExampleRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/attempt")
 */
class AttemptController extends Controller
{
    use BaseTrait;

    /**
     * @Route("/", name="attempt_index")
     */
    public function index(AttemptRepository $attemptRepository)
    {
        return $this->render('attempt/index.html.twig', [
            'attempts' => $attemptRepository->findAllByCurrentUser(),
        ]);
    }

    /**
     *@Route("/{id}/show", name="attempt_show", requirements={"id": "\d+"})
     */
    public function show(Attempt $attempt, ExampleRepository $exampleRepository, AttemptRepository $attemptRepository)
    {
        $this->denyAccessUnlessGranted('VIEW', $attempt);

        return $this->render('attempt/show.html.twig', [
            'att' => $attempt->setEntityRepository($attemptRepository),
            'examples' => $exampleRepository->findByAttempt($attempt),
            'exR' => $exampleRepository,
        ]);
    }

    /**
     *@Route("/{id}", name="attempt_solve", requirements={"id": "\d+"})
     */
    public function solve(Attempt $attempt, ExampleRepository $exampleRepository, AttemptRepository $attemptRepository)
    {
        if (!$this->isGranted('SOLVE', $attempt)) {
            if ($this->isGranted('VIEW', $attempt)) {
                return $this->redirectToRoute('attempt_show', ['id' => $attempt->getId()]);
            } else {
                throw new AccessDeniedException();
            }
        }

        $exampleRepository->findLastUnansweredByAttemptOrGetNew($attempt);

        return $this->render('attempt/solve.html.twig', [
            'jsParams' => [
                'attData' => $attempt->setEntityRepository($attemptRepository)->getData(),
                'attempt_answer' => $this->generateUrl('attempt_answer', ['id' => $attempt->getId()]),
            ],
            'att' => $attempt,
        ]);
    }

    /**
     *@Route("/last", name="attempt_last")
     */
    public function last(AttemptRepository $attemptRepository)
    {
        if ($attempt = $attemptRepository->findLastActualByCurrentUser()) {
            return $this->redirectToRoute('attempt_solve', ['id' => $attempt->getId()]);
        }

        return $this->redirectToRoute('attempt_index');
    }

    /**
     *@Route("/new", name="attempt_new")
     */
    public function new(AttemptRepository $attemptRepository)
    {
        return $this->redirectToRoute('attempt_solve', [
            'id' => $attemptRepository->getNewByCurrentUser()->getId(),
        ]);
    }

    /**
     *@Route("/{id}/answer", name="attempt_answer", methods="POST")
     */
    public function answer(Attempt $attempt, Request $request, ExampleRepository $exampleRepository, EntityManagerInterface $entityManager, AttemptRepository $attemptRepository)
    {
$showAttemptUrl=['targetUrl' => $this->generateUrl('attempt_show', ['id'=>$attempt->getId()])];

        if (!$this->isGranted('ANSWER', $attempt)) {
            return $this->json(['finish' => true] + $showAttemptUrl);
        }

        $example = $exampleRepository->findLastUnansweredByAttempt($attempt);
        $answer = (float) $request->request->get('answer');
        $example->setAnswer($answer);
        $entityManager->flush();

        $finish = !$this->isGranted('SOLVE', $attempt);

        if (!$finish) {
            $exampleRepository->getNew($attempt);
        }

        return $this->json([
            'isRight' => $example->isRight(),
            'finish' => $finish,
            'attData' => $attempt->setEntityRepository($attemptRepository)->getData(),
        ]
 + $showAttemptUrl);
    }

    /**
     *@Route("/{id}/profile", name="attempt_profile")
     */
    public function profile(Attempt $attempt, ProfileRepository $profileRepository, AttemptRepository $attemptRepository)
    {
        $this->denyAccessUnlessGranted('VIEW', $attempt);
        $profile = $attempt->getSettings()->setEntityRepository($profileRepository);

        return $this->render('attempt/profile.html.twig', [
            'jsParams' => [
                'canEdit' => false,
            ],
            'profile' => $profile,
            'form' => $this->createForm(ProfileType::class, $profile)->createView(),
            'att' => $attempt->setEntityRepository($attemptRepository),
        ]);
    }
}
