<?php

declare(strict_types=1);

namespace App\Controller;

use App\ApiPlatform\Attribute;
use App\ApiPlatform\Filter\Validation\FilterTaskValidationSubscriber;
use App\ApiPlatform\Filter\Validation\FilterUserValidationSubscriber;
use App\ApiPlatform\Format;
use App\Attempt\EventSubscriber\ShowAttemptsCollectionSubscriber;
use App\Attempt\Example\EventSubscriber\ShowExamplesCollectionSubscriber;
use App\Attempt\Profile\ProfileProviderInterface;
use App\Attempt\Settings\SettingsProviderInterface;
use App\Controller\Traits\CurrentUserProviderTrait;
use App\Controller\Traits\JavascriptParametersTrait;
use App\Entity\Task;
use App\Entity\User;
use App\Entity\User\Role;
use App\Form\TaskType;
use App\Object\ObjectAccessor;
use App\Response\Result\ContractorResult;
use App\Security\Annotation as AppSecurity;
use App\Security\Voter\TaskVoter;
use App\Security\Voter\UserVoter;
use App\Task\Contractor\ContractorProviderInterface;
use App\Task\Contractor\ContractorResultFactoryInterface;
use App\Task\TaskProviderInterface;
use App\Task\TaskResultFactoryInterface;
use App\User\Teacher\Exception\RequiresTeacherAccessException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/task")
 * @IsGranted(Role::USER)
 * @AppSecurity\IsGranted(Role::TEACHER, exception=RequiresTeacherAccessException::class)
 */
final class TaskController extends Controller
{
    use CurrentUserProviderTrait, JavascriptParametersTrait;

    /**
     * @Route("/", name="task_index", methods="GET")
     */
    public function index(TaskProviderInterface $taskProvider, TaskResultFactoryInterface $taskResultFactory): Response
    {
        $createTaskResult = [$taskResultFactory, 'createTaskResult'];

        return $this->render('task/index.html.twig', [
            'actual' => array_map($createTaskResult, $taskProvider->getActualTasksOfCurrentUser()),
            'archive' => array_map($createTaskResult, $taskProvider->getArchiveTasksOfCurrentUser()),
        ]);
    }

    /**
     * @Route("/new/", name="task_new", methods={"GET", "POST"})
     */
    public function new(Request $request, SettingsProviderInterface $settingsProvider, ProfileProviderInterface $profileProvider): Response
    {
        /** @var Task $task */
        $task = ObjectAccessor::initialize(Task::class, [
            'author' => $this->getCurrentUserOrGuest(),
        ]);
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($task);
            $entityManager->flush($task);

            return $this->redirectToRoute('task_index');
        }

        $form->remove('profile');

        return $this->render('task/new.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
            'profileProvider' => $profileProvider,
            'currentProfile' => $profileProvider->getCurrentProfile(),
            'publicProfiles' => $profileProvider->getPublicProfiles(),
            'userProfiles' => $profileProvider->getCurrentUserProfiles(),
        ]);
    }

    /**
     * @Route("/{id}/", name="task_show", methods="GET")
     * @IsGranted(TaskVoter::SHOW, subject="task")
     */
    public function show(Task $task, ContractorProviderInterface $contractorProvider, ContractorResultFactoryInterface $contractorResultFactory, TaskResultFactoryInterface $taskResultFactory): Response
    {
        $createContractorResult = function (User $contractor) use ($task, $contractorResultFactory): ContractorResult {
            return $contractorResultFactory->createContractorResult($contractor, $task);
        };

        return $this->render('task/show.html.twig', [
            'taskResult' => $taskResultFactory->createTaskResult($task),
            'solvedTaskContractors' => array_map($createContractorResult, $contractorProvider->getSolvedTaskContractors($task)),
            'notSolvedTaskContractors' => array_map($createContractorResult, $contractorProvider->getNotSolvedTaskContractors($task)),
        ]);
    }

    /**
     * @Route("/{id}/edit/", name="task_edit", methods={"GET", "POST"})
     * @IsGranted(TaskVoter::EDIT, subject="task")
     */
    public function edit(Request $request, Task $task, ProfileProviderInterface $profileProvider): Response
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()
                ->getManager()
                ->flush($task);

            return $this->redirectToRoute('task_index');
        }

        $form->remove('profile');

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
            'profileProvider' => $profileProvider,
            'currentProfile' => $profileProvider->getSettingsOrDefaultProfile($task->getSettings()),
            'publicProfiles' => $profileProvider->getPublicProfiles(),
            'userProfiles' => $profileProvider->getCurrentUserProfiles(),
        ]);
    }

    /**
     * @Route("/{id}/contractor/{contractor_id}/attempts/", name="task_contractor_attempts", methods="GET")
     * @Entity("user", expr="repository.find(contractor_id)")
     * @IsGranted(TaskVoter::SHOW, subject="task")
     * @IsGranted(UserVoter::SHOW_SOLVING_RESULTS, subject="user")
     */
    public function contractorAttempts(Task $task, User $user): Response
    {
        $this->setJavascriptParameters([
            'getAttemptsUrl' => $this->generateUrl(ShowAttemptsCollectionSubscriber::ROUTE, [FilterUserValidationSubscriber::FIELD => $user->getUsername(), FilterTaskValidationSubscriber::FIELD => $task->getId(), Attribute::FORMAT => Format::JSONDT]),
        ]);

        return $this->render('task/contractor_attempts.html.twig', [
            'task' => $task,
            'contractor' => $user,
        ]);
    }

    /**
     * @Route("/{id}/contractor/{contractor_id}/examples/", name="task_contractor_examples", methods="GET")
     * @Entity("user", expr="repository.find(contractor_id)")
     * @IsGranted(TaskVoter::SHOW, subject="task")
     * @IsGranted(UserVoter::SHOW_SOLVING_RESULTS, subject="user")
     */
    public function contractorExamples(Task $task, User $user): Response
    {
        $this->setJavascriptParameters([
            'getExamplesUrl' => $this->generateUrl(ShowExamplesCollectionSubscriber::ROUTE, [FilterUserValidationSubscriber::FIELD => $user->getUsername(), FilterTaskValidationSubscriber::FIELD => $task->getId(), Attribute::FORMAT => Format::JSONDT]),
        ]);

        return $this->render('task/contractor_examples.html.twig', [
            'task' => $task,
            'contractor' => $user,
        ]);
    }

    /**
     * @Route("/{id}/archive/", name="task_archive", methods={"GET"})
     * @IsGranted(TaskVoter::EDIT, subject="task")
     */
    public function archive(Task $task): RedirectResponse
    {
        $lastTime = (new \DateTime())->sub(new \DateInterval('PT1M'));
        $task->setAddTime($lastTime);
        $task->setLimitTime($lastTime);
        $this->getDoctrine()->getManager()->flush($task);

        return $this->redirectToRoute('task_index');
    }
}
