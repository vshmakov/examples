<?php

namespace App\Controller;

use App\Entity\Settings;
use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use App\Repository\AttemptRepository;
use App\Repository\ExampleRepository;
use App\Repository\ProfileRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Service\UserLoader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/task")
 */
class TaskController extends AbstractController
{
    /**
     * @Route("/", name="task_index", methods="GET")
     */
    public function index(TaskRepository $taskRepository): Response
    {
        $this->denyAccessUnlessGranted('SHOW_TASKS');

        $tasks = array_reduce(
            $taskRepository->findByCurrentAuthor(),
            function (array $data, Task $task) use ($taskRepository): array {
                $group = $taskRepository->isActual($task) ? 'actualTasks' : 'archiveTasks';
                $data[$group][] = $task;

                return $data;
            },
            ['actualTasks' => [], 'archiveTasks' => []]
        );

        return $this->render('task/index.html.twig', [
            'taskRepository' => $taskRepository,
        ]
            + $tasks);
    }

    /**
     * @Route("/new", name="task_new", methods="GET|POST")
     */
    public function new(Request $request, UserLoader $userLoader, ProfileRepository $profileRepository, UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('CREATE_TASKS');

        $currentUser = $userLoader->getUser()
            ->setEntityRepository($userRepository);
        $task = (new Task())
            ->setAuthor($currentUser)
            ->setContractors($currentUser->getStudents())
            ->setLimitTime((new \DT())->add(new \DateInterval('P7D')));
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $redirectResponse = $this->processForm($form, $request, $profileRepository)) {
            return $redirectResponse;
        }

        return $this->render('task/new.html.twig', [
            'jsParams' => [
                'current' => $currentUser->getCurrentProfile()->getId(),
            ],
            'task' => $task,
            'form' => $form->createView(),
            'publicProfiles' => $profileRepository->findByIsPublic(true),
            'profiles' => $profileRepository->findByAuthor($currentUser),
            'profileRepository' => $profileRepository,
        ]);
    }

    /**
     * @Route("/{id}", name="task_show", methods="GET")
     */
    public function show(Task $task, UserRepository $userRepository, TaskRepository $taskRepository, AttemptRepository $attemptRepository): Response
    {
        $this->denyAccessUnlessGranted('SHOW', $task);

        $contractors = $userRepository->findByHomework($task);
        $finishedTaskContractors = $notFinishedTaskContractors = [];

        foreach ($contractors as $contractor) {
            $group = $taskRepository->isDoneByUser($task, $contractor) ? 'finishedTaskContractors' : 'notFinishedTaskContractors';
            ($$group)[] = $contractor;
        }

        return $this->render('task/show.html.twig', [
            'task' => $task,
            'finishedTaskContractors' => $finishedTaskContractors,
            'notFinishedTaskContractors' => $notFinishedTaskContractors,
            'attemptRepository' => $attemptRepository,
        ]);
    }

    private function processForm(Form $form, Request $request, ProfileRepository $profileRepository): ? RedirectResponse
    {
        $task = $form->getData();
        $profile = $profileRepository->find($request->request->get('profile_id', ''));

        if (!$profile) {
            throw $this->createNotFoundException();
        }

        if (!$this->isGranted('appoint', $profile)) {
            throw $this->createAccessDenyedException();
        }

        if ($form->isValid()) {
            $settings = new Settings();
            Settings::copySettings($profile, $settings);
            $task->setSettings($settings);

            $em = $this->getDoctrine()->getManager();
            $em->persist($settings);
            $em->persist($task);
            $em->flush();

            return $this->redirectToRoute('task_index');
        }

        return null;
    }

    /**
     * @Route("/{id}/edit", name="task_edit", methods="GET|POST")
     */
    public function edit(Request $request, Task $task, ProfileRepository $profileRepository, UserRepository $userRepository, UserLoader $userLoader): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $task);

        $currentUser = $userLoader->getUser()
            ->setEntityRepository($userRepository);
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $redirectResponse = $this->processForm($form, $request, $profileRepository)) {
            return $redirectResponse;
        }

        return $this->render('task/edit.html.twig', [
            'jsParams' => [
                'current' => $currentUser->getCurrentProfile()->getId(),
            ],
            'task' => $task,
            'form' => $form->createView(),
            'publicProfiles' => $profileRepository->findByIsPublic(true),
            'profiles' => $profileRepository->findByAuthor($currentUser),
            'profileRepository' => $profileRepository,
        ]);
    }

    /**
     * @Route("/{id}", name="task_delete", methods="DELETE")
     */
    public function delete(Request $request, Task $task): Response
    {
        throw $this->createNotFoundException();
        $this->denyAccessUnlessGranted('DELETE', $task);

        if ($this->isCsrfTokenValid('delete'.$task->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($task);
            $em->flush();
        }

        return $this->redirectToRoute('task_index');
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
