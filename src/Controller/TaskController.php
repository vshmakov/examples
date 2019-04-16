<?php

namespace App\Controller;

use App\Attempt\Profile\ProfileProviderInterface;
use App\Attempt\Settings\SettingsProviderInterface;
use App\Controller\Traits\CurrentUserProviderTrait;
use App\Entity\Task;
use App\Entity\User;
use App\Entity\User\Role;
use App\Form\TaskType;
use App\Object\ObjectAccessor;
use App\Repository\AttemptRepository;
use App\Repository\ExampleRepository;
use App\Repository\ProfileRepository;
use App\Repository\SettingsRepository;
use App\Repository\UserRepository;
use App\Response\Result\ContractorResult;
use App\Security\Annotation as AppSecurity;
use App\Security\Voter\TaskVoter;
use App\Service\UserLoader;
use App\Task\Contractor\ContractorProviderInterface;
use App\Task\Contractor\ContractorResultFactoryInterface;
use App\Task\TaskProviderInterface;
use App\Task\TaskResultFactoryInterface;
use App\User\Teacher\Exception\RequiresTeacherAccessException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/task")
 * @AppSecurity\IsGranted(Role::TEACHER, exception=RequiresTeacherAccessException::class)
 */
final class TaskController extends Controller
{
    use CurrentUserProviderTrait;

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
        $currentUser = $this->getCurrentUserOrGuest();
        $task = ObjectAccessor::initialize(Task::class, [
            'author' => $currentUser,
            'contractors' => $currentUser->getStudents(),
            'limitTime' => (new \DateTime())->add(new \DateInterval('P7D')),
        ]);
        $publicProfiles = $profileProvider->getPublicProfiles();
        $userProfiles = $profileProvider->getCurrentUserProfiles();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $objectManager = $this->getDoctrine()->getManager();
            $objectManager->persist($task);
            $objectManager->flush($task);

            return $this->redirectToRoute('task_index');
        }

        $form->remove('profile');

        return $this->render('task/new.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
            'profileProvider' => $profileProvider,
            'publicProfiles' => $publicProfiles,
            'userProfiles' => $userProfiles,
        ]);
    }

    /**
     * @Route("/{id}/", name="task_show", methods="GET")
     * @IsGranted(TaskVoter::SHOW, subject="task")
     */
    public function show(Task $task, ContractorProviderInterface $contractorProvider, ContractorResultFactoryInterface $contractorResponseProvider): Response
    {
        $createContractorResponse = function (User $contractor) use ($task, $contractorResponseProvider): ContractorResult {
            return $contractorResponseProvider->createContractorResult($contractor, $task);
        };

        return $this->render('task/show.html.twig', [
            'task' => $task,
            'solvedTaskContractors' => array_map($createContractorResponse, $contractorProvider->getSolvedTaskContractors($task)),
            'notSolvedTaskContractors' => array_map($createContractorResponse, $contractorProvider->getNotSolvedTaskContractors($task)),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="task_edit", methods="GET|POST")
     */
    public function edit(Request $request, Task $task, ProfileRepository $profileRepository, UserRepository $userRepository, UserLoader $userLoader, SettingsRepository $settingsRepository): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $task);

        $currentUser = $userLoader->getUser()
            ->setEntityRepository($userRepository);
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $redirectResponse = $this->saveAndRedirect($form, $request, $profileRepository, $settingsRepository)) {
            return $redirectResponse;
        }

        return $this->render('task/edit.html.twig', [
            'jsParams' => [
                'current' => ($profileRepository->findOneByCurrentAuthorOrPublicAndSettingsData($task->getSettings()) ?? $currentUser->getCurrentProfile())->getId(),
            ],
            'task' => $task,
            'form' => $form->createView(),
            'publicProfiles' => $profileRepository->findByIsPublic(true),
            'profiles' => $profileRepository->findByAuthor($currentUser),
            'profileRepository' => $profileRepository,
        ]);
    }

    /**
     * @Route("/{id}/contractor/{contractor_id}/attempts", name="task_contractor_attempts", methods="GET")
     * @Entity("user", expr="repository.find(contractor_id)")
     */
    public function contractorAttempts(Task $task, User $user, AttemptRepository $attemptRepository): Response
    {
        $this->denyAccessUnlessGranted('SHOW', $task);
        $this->denyAccessUnlessGranted('SHOW_ATTEMPTS', $user);

        return $this->render('task/contractor_attempts.html.twig', [
            'task' => $task,
            'contractor' => $user,
            'attempts' => $attemptRepository->findByUserAndTask($user, $task),
        ]);
    }

    /**
     * @Route("/{id}/contractor/{contractor_id}/examples", name="task_contractor_examples", methods="GET")
     * @Entity("user", expr="repository.find(contractor_id)")
     */
    public function contractorExamples(Task $task, User $user, ExampleRepository $exampleRepository): Response
    {
        $this->denyAccessUnlessGranted('SHOW', $task);
        $this->denyAccessUnlessGranted('SHOW_EXAMPLES', $user);

        return $this->render('task/contractor_examples.html.twig', [
            'task' => $task,
            'contractor' => $user,
            'examples' => $exampleRepository->findByUserAndTask($user, $task),
        ]);
    }
}
