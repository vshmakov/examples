<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\UserLoader;
use App\Entity\User;

/**
 * @Route("/teacher")
 */
class TeacherController extends MainController
{
    private $u;

    public function __construct(UserRepository $uR, UserLoader $ul)
    {
        $this->u = $ul->getUser()->setER($uR);
    }

    /**
     *@Route("/", name="teacher_index")
     */
    public function index(UserRepository $uR)
    {
        return $this->render('teacher/index.html.twig', [
            'teachers' => $uR->findByIsTeacher(true),
        ]);
    }

    /**
     *@Route("/appoint/{id}", name="teacher_appoint")
     */
    public function appoint(User $teacher)
    {
        if ($this->isGranted('APPOINT_TEACHER', $teacher)) {
            $this->u->setTeacher($teacher);
            $this->em()->flush();
        }

        return $this->redirectToRoute('account_index');
    }

    /**
     *@Route("/disappoint", name="teacher_disappoint")
     */
    public function disappoint()
    {
        $this->u->setTeacher(null);
        $this->em()->flush();

        return $this->redirectToRoute('account_index');
    }
}
