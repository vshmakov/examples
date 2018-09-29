<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\AttemptRepository;
use App\Repository\ExampleRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\UserLoader;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/student")
 */
class StudentController extends Controller
{
    use BaseTrait;

    private $currentUser;

    public function __construct(UserRepository $userRepository, UserLoader $userLoader)
    {
        $this->currentUser = $userLoader->getUser()
            ->setEntityRepository($userRepository);
    }

    /**
     *@Route("/", name="student_index")
     */
    public function index(UserRepository $userRepository)
    {
        return $this->render('student/index.html.twig', [
            'students' => $this->currentUser->getStudents()->getValues(),
            'uR' => $userRepository,
        ]);
    }

    /**
     *@Route("/{id}/attempts", name="student_attempts")
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
     *@Route("/{id}/examples", name="student_examples")
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
