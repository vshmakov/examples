<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\UserLoader;

/**
 * @Route("/student")
 */
class StudentController extends MainController
{
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
}
