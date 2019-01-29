<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\User\Role;
use App\Exception\RequiresTeacherAccessException;
use App\Repository\AttemptRepository;
use App\Repository\ExampleRepository;
use App\Repository\UserRepository;
use App\Security\Annotation as AppSecurity;
use App\Service\UserLoader;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/student")
 * @AppSecurity\IsGranted(Role::TEACHER, exception=RequiresTeacherAccessException::class)
 */
final class StudentController extends Controller
{
    use BaseTrait;
    private $currentUser;

    public function __construct(UserRepository $userRepository, UserLoader $userLoader)
    {
        $this->currentUser = $userLoader->getUser()
            ->setEntityRepository($userRepository);
    }

    /**
     * @Route("/", name="student_index")
     */
    public function index(UserRepository $userRepository)
    {
        $currentUser = $this->currentUser;

        return $this->render('student/index.html.twig', [
            'students' => $currentUser->getRealStudents()->getValues(),
            'children' => $currentUser->getChildren()->getValues(),
            'userRepository' => $userRepository,
            'DTSubDays' => function (int $day): \DateTimeInterface {
                return \DT::createBySubDays($day);
            },
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
