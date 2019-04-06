<?php

namespace App\Controller;

use App\Controller\Traits\CurrentUserProviderTrait;
use App\Entity\User;
use App\Entity\User\Role;
use App\Repository\AttemptRepository;
use App\Repository\ExampleRepository;
use App\Security\Annotation as AppSecurity;
use App\User\Teacher\Exception\RequiresTeacherAccessException;
use App\User\UserEvaluatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/student")
 * @AppSecurity\IsGranted(Role::TEACHER, exception=RequiresTeacherAccessException::class)
 */
final class StudentController extends Controller
{
    use  CurrentUserProviderTrait;

    /**
     * @Route("/", name="student_index")
     */
    public function index(UserEvaluatorInterface $userEvaluator): Response
    {
        $students = $this->getCurrentUserOrGuest()->getRealStudents()->getValues();
        $this->sortStudents($students);

        return $this->render('student/index.html.twig', [
            'students' => $students,
            'userEvaluator' => $userEvaluator,
        ]);
    }

    /**
     * @Route("/{id}/attempts", name="student_attempts")
     */
    public function attempts(User $student, AttemptRepository $attemptRepository)
    {
        $this->denyAccessUnlessGranted('SHOW_ATTEMPTS', $student);

        return $this->render('student/attempts.html.twig', [
            'attempts' => $attemptRepository->findByUser($student),
            'student' => $student,
        ]);
    }

    /**
     * @Route("/{id}/examples", name="student_examples")
     */
    public function examples(User $student, ExampleRepository $exampleRepository)
    {
        $this->denyAccessUnlessGranted('SHOW_EXAMPLES', $student);

        return $this->render('student/examples.html.twig', [
            'student' => $student,
            'examples' => $exampleRepository->findByUser($student),
        ]);
    }

    private function sortStudents(array &$students): void
    {
        usort($students, function (User $student1, User $student2): int {
            $attempt1 = $student1->getLastAttempt();
            $attempt2 = $student2->getLastAttempt();

            if (null !== $attempt1 && null !== $attempt2 && !$attempt1->getStartedAt()->isEqualTo($attempt2->getCreatedAt())) {
                return $attempt1->getStartedAt()->isGreaterThan($attempt2->getStartedAt()) ? 1 : -1;
            }
        });
    }
}
