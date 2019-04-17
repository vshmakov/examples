<?php

namespace App\Controller;

use App\Attempt\AttemptFactoryInterface;
use App\Entity\Task;
use App\Security\Annotation as AppSecurity;
use App\Security\Voter\TaskVoter;
use App\Task\Contractor\ContractorProviderInterface;
use App\Task\Homework\HomeworkProviderInterface;
use App\User\Student\Exception\RequiresStudentAccessException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/homework")
 * @AppSecurity\IsGranted("ROLE_STUDENT", exception=RequiresStudentAccessException::class)
 */
final class HomeworkController extends Controller
{
    /**
     * @Route("/", name="homework_index", methods={"GET"})
     */
    public function index(HomeworkProviderInterface $homeworkProvider): Response
    {
        return $this->render('homework/index.html.twig', [
            'actualHomework' => $homeworkProvider->getActualHomework(),
            'archiveHomework' => $homeworkProvider->getArchiveHomework(),
        ]);
    }

    /**
     * @Route("{id}/solve/", name="homework_solve", methods={"GET"})
     * @IsGranted(TaskVoter::SOLVE, subject="task")
     */
    public function solve(Task $task, AttemptFactoryInterface $attemptFactory, ContractorProviderInterface $contractorProvider): RedirectResponse
    {
        if ($contractorProvider->isDoneByCurrentContractor($task)) {
            throw new BadRequestHttpException();
        }

        return $this->redirectToRoute('attempt_solve', [
            'id' => $attemptFactory->createCurrentUserSolvesTaskAttempt($task)->getId(),
        ]);
    }
}
