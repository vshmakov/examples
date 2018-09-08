<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\AttemptRepository;
use App\Repository\ExampleRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\UserLoader;
use App\Entity\User;

/**
 * @Route("/student")
 */
class StudentController extends MainController
{
    use ApiTrait;

    private $u;

    public function __construct(UserRepository $uR, UserLoader $ul)
    {
        $this->u = $ul->getUser()->setER($uR);
    }

    /**
     *@Route("/", name="student_index")
     */
    public function index(UserRepository $uR)
    {
        return $this->render('student/index.html.twig', [
            'students' => $this->u->getStudents()->getValues(),
            'uR' => $uR,
        ]);
    }

    /**
     *@Route("/{id}/attempts", name="student_attempts")
     */
    public function attempts(User $student, AttemptRepository $attR)
    {
        $this->denyAccessUnlessGranted('SHOW_ATTEMPTS', $student);

        return $this->render('student/attempts.html.twig', [
            'data' => $this->processAttempts($attR->findByUser($student), $attR),
            'student' => $student,
        ]);
    }

    /**
     *@Route("/{id}/examples", name="student_examples")
     */
    public function examples(User $student, ExampleRepository $exR)
    {
        $this->denyAccessUnlessGranted('SHOW_EXAMPLES', $student);

        return $this->render('student/examples.html.twig', [
            'student' => $student,
            'examples' => $exR->findByUser($student),
        ]);
    }
}
