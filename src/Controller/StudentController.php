<?php

namespace App\Controller;

use App\Controller\Traits\BaseTrait;
use App\Controller\Traits\CurrentUserProviderTrait;
use  App\DateTime\DateTime as DT;
use App\Entity\User;
use App\Entity\User\Role;
use App\Repository\AttemptRepository;
use App\Repository\ExampleRepository;
use App\Repository\UserRepository;
use App\Security\Annotation as AppSecurity;
use App\Service\UserLoader;
use App\User\Teacher\Exception\RequiresTeacherAccessException;
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
    public function index(): Response
    {

        return $this->render('student/index.html.twig', [
            'students' => $this->getCurrentUserOrGuest()->getRealStudents(),
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
}
