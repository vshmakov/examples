<?php

declare(strict_types=1);

namespace App\Controller;

use App\ApiPlatform\Attribute;
use App\ApiPlatform\Filter\Validation\FilterUserValidationSubscriber;
use App\ApiPlatform\Format;
use App\Attempt\AttemptFactoryInterface;
use App\Attempt\AttemptProviderInterface;
use App\Attempt\AttemptResponseFactoryInterface;
use App\Attempt\EventSubscriber\ShowAttemptsCollectionSubscriber;
use App\Attempt\Example\ExampleResponseFactoryInterface;
use App\Controller\Traits\CurrentUserProviderTrait;
use App\Controller\Traits\JavascriptParametersTrait;
use App\Controller\Traits\ProfileTrait;
use App\Entity\Attempt;
use App\Entity\Profile;
use App\Iterator;
use App\Security\Voter\AttemptVoter;
use App\Security\Voter\ProfileVoter;
use App\Task\Contractor\ContractorResultFactoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/attempt")
 */
final class AttemptController extends Controller
{
    use CurrentUserProviderTrait, JavascriptParametersTrait, ProfileTrait;

    /**
     * @Route("/", name="attempt_index", methods={"GET"})
     */
    public function index(): Response
    {
        $this->setJavascriptParameters([
            'getAttemptsUrl' => $this->generateUrl(ShowAttemptsCollectionSubscriber::ROUTE, [FilterUserValidationSubscriber::FIELD => $this->getCurrentUserOrGuest()->getUsername(), Attribute::FORMAT => Format::JSONDT]),
        ]);

        return $this->render('attempt/index.html.twig');
    }

    /**
     * @Route("/last/", name="attempt_last", methods={"GET"})
     */
    public function last(AttemptProviderInterface $attemptProvider): RedirectResponse
    {
        if ($attempt = $attemptProvider->getLastAttempt()) {
            return $this->redirectToRoute('attempt_solve', ['id' => $attempt->getId()]);
        }

        return $this->redirectToRoute('attempt_index');
    }

    /**
     * @Route("/new/", name="attempt_new", methods={"GET"})
     */
    public function new(Request $request, AttemptFactoryInterface $creator): RedirectResponse
    {
        $profileParameter = 'profile_id';

        if ($request->query->has($profileParameter)) {
            $profile = $this->getDoctrine()
                ->getRepository(Profile::class)
                ->find($request->query->get($profileParameter));

            if (null === $profile) {
                throw new NotFoundHttpException();
            }

            $this->denyAccessUnlessGranted(ProfileVoter::APPOINT, $profile);
            $this->saveAndAppointProfile($profile);
        }

        return $this->redirectToRoute('attempt_solve', [
            'id' => $creator->createCurrentUserAttempt()->getId(),
        ]);
    }

    /**
     * @Route("/{id}/show/", name="attempt_show", methods={"GET"})
     * @IsGranted(AttemptVoter::VIEW, subject="attempt")
     */
    public function show(Attempt $attempt, AttemptResponseFactoryInterface $attemptResponseFactory, ExampleResponseFactoryInterface $exampleResponseFactory, ContractorResultFactoryInterface $contractorResultFactory): Response
    {
        $hasNextTaskAttempt = false;
        $contractorResult = null;

        if (null !== $attempt->getTask()) {
            $contractorResult = $contractorResultFactory->createCurrentContractorResult($attempt->getTask());
            $hasNextTaskAttempt = !$contractorResult->isDone();
        }

        return $this->render('attempt/show.html.twig', [
            'attempt' => $attempt,
            'hasNextTaskAttempt' => $hasNextTaskAttempt,
            'contractorResult' => $contractorResult,
            'attemptResponse' => $attemptResponseFactory->createAttemptResponse($attempt),
            'exampleResponses' => Iterator::map($attempt->getExamples(), [$exampleResponseFactory, 'createExampleResponse']),
        ]);
    }

    /**
     * @Route("/{id}/", name="attempt_solve", methods={"GET"})
     * @IsGranted(AttemptVoter::SOLVE, subject="attempt")
     */
    public function solve(Attempt $attempt, AttemptResponseFactoryInterface $attemptResponseProvider): Response
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
     * @Route("/{id}/settings/", name="attempt_settings", methods={"GET"})
     * @IsGranted(AttemptVoter::VIEW, subject="attempt")
     */
    public function settings(Attempt $attempt, AttemptResponseFactoryInterface $attemptResponseProvider): Response
    {
        return $this->render('attempt/settings.html.twig', [
            'attempt' => $attempt,
            'settings' => $attempt->getSettings(),
            'attemptResponse' => $attemptResponseProvider->createAttemptResponse($attempt),
        ]);
    }
}
