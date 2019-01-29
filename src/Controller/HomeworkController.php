<?php

namespace App\Controller;

use App\Entity\Task;
use App\Exception\RequiresStudentAccessException;
use App\Repository\AttemptRepository;
use App\Repository\ExampleRepository;
use App\Repository\TaskRepository;
use App\Security\Annotation as AppSecurity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/homework")
 * @AppSecurity\IsGranted("ROLE_STUDENT", exception=RequiresStudentAccessException::class)
 */
class HomeworkController extends AbstractController
{
    use BaseTrait;

    /**
     * @Route("/", name="homework_index")
     */
    public function index(TaskRepository $taskRepository, AttemptRepository $attemptRepository): Response
    {
        $this->denyAccessUnlessGranted('SHOW_HOMEWORKS');

        $tasks = $taskRepository->findHomeworkByCurrentUser();
        $activeTasks = $archiveTasks = [];

        foreach ($tasks as $task) {
            $group = $this->isGranted('SOLVE', $task) ? 'activeTasks' : 'archiveTasks';
            ($$group)[] = $task;
        }

        return $this->render('homework/index.html.twig', [
            'activeTasks' => $activeTasks,
            'archiveTasks' => $archiveTasks,
            'attemptRepository' => $attemptRepository,
        ]);
    }

    /**
     * @Route("{id}/solve", name="homework_solve")
     */
    public function solve(Task $task, AttemptRepository $attemptRepository): Response
    {
        $this->denyAccessUnlessGranted('SOLVE', $task);

        return $this->redirectToRoute('attempt_solve', [
            'id' => $attemptRepository->getNewByCurrentUserAndTask($task)->getId(),
        ]);
    }

    /**
     * @Route("{id}/examples", name="homework_examples")
     */
    public function examples(Task $task, ExampleRepository $exampleRepository): Response
    {
        $this->denyAccessUnlessGranted('SHOW_EXAMPLES', $task);

        return $this->render('homework/examples.html.twig', [
            'task' => $task,
            'examples' => $exampleRepository->findByCurrentUserAndHomework($task),
        ]);
    }

    /**
     * @Route("{id}/attempts", name="homework_attempts")
     */
    public function attempts(Task $task, AttemptRepository $attemptRepository): Response
    {
        $this->denyAccessUnlessGranted('SHOW_ATTEMPTS', $task);

        return $this->render('homework/attempts.html.twig', [
            'task' => $task,
            'attempts' => $attemptRepository->findByCurrentUserAndHomework($task),
        ]);
    }
}
