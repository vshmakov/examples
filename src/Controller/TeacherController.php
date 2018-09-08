<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Form\AccountType;
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
    public function appoint(User $teacher, ValidatorInterface $validator, Request $request)
    {
        $errors = $validator->validate($this->u);

        if (!count($errors)) {
            if ($this->isGranted('APPOINT', $teacher)) {
                $this->u->setTeacher($teacher);
                $this->em()->flush();
            }

            return $this->redirectToRoute('account_index');
        } else {
            $u = clone $this->u;
            $u->cleanSocialUsername();
            $form = $this->createForm(AccountType::class, $u);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $u = ($this->u);
                $u->cleanSocialUsername();
                $form = $this->createForm(AccountType::class, $u);
                $form->handleRequest($request);
                $this->em()->flush();

                return $this->redirectToRoute('teacher_appoint', [
                    'id' => $teacher->getId(),
                ]);
            }

            foreach ($errors as $er) {
                $form->addError(new FormError($er->getMessage()));
            }
            $form->remove('isTeacher');

            return $this->render('teacher/edit.html.twig', [
                'form' => $form->createView(),
            ]);
        }
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
