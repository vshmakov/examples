<?php

namespace App\Controller;

use App\Attempt\AttemptFactoryInterface;
use App\Entity\Task;
use App\Entity\User\Role;
use App\Security\Annotation as AppSecurity;
use App\Security\Voter\CurrentUserVoter;
use App\Security\Voter\TaskVoter;
use App\Task\Contractor\ContractorProviderInterface;
use App\Task\Contractor\ContractorResultFactoryInterface;
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
 * @IsGranted(Role::USER)
 * @AppSecurity\IsGranted("ROLE_STUDENT", exception=RequiresStudentAccessException::class)
 * @IsGranted(CurrentUserVoter::SHOW_HOMEWORK, message="Необходимо выбрать учителя")
 */
final class HomeworkController extends Controller
{
    /**
     * @Route("/", name="homework_index", methods={"GET"})
     */
    public function index(HomeworkProviderInterface $homeworkProvider, ContractorResultFactoryInterface $contractorResultFactory): Response
    {
        return $this->render('homework/index.html.twig', [
            'actualHomework' => $contractorResultFactory->mapCreateCurrentContractorResult($homeworkProvider->getActualHomework()),
            'archiveHomework' => $contractorResultFactory->mapCreateCurrentContractorResult($homeworkProvider->getArchiveHomework()),
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
