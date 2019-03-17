<?php

namespace App\Controller;

use App\Controller\Traits\BaseTrait;
use App\Entity\User;
use App\Exception\RequiresStudentAccessException;
use App\Form\ChildType;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Security\Annotation as AppSecurity;
use App\Service\UserLoader;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/teacher")
 * @AppSecurity\IsGranted("ROLE_STUDENT", exception=RequiresStudentAccessException::class)
 */
final class TeacherController extends Controller
{
    use BaseTrait;
    private $currentUser;

    public function __construct(UserRepository $userRepository, UserLoader $userLoader)
    {
        $this->currentUser = $userLoader->getUser()
            ->setEntityRepository($userRepository);
    }

    /**
     * @Route("/", name="teacher_index")
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('teacher/index.html.twig', [
            'teachers' => $userRepository->findByIsTeacher(true),
        ]);
    }

    /**
     * @Route("/{id}/appoint", name="teacher_appoint")
     */
    public function appoint(User $teacher, ValidatorInterface $validator, Request $request, TaskRepository $taskRepository): Response
    {
        $this->denyAccessUnlessGranted('APPOINT', $teacher);
        $currentUser = $this->currentUser;
        $currentUser->cleanSocialUsername();
        $errors = $validator->validate($currentUser, null, ['child']);

        if (!\count($errors)) {
            $currentUser->setTeacher($teacher);

            foreach ($taskRepository->findActualByAuthor($teacher) as $homework) {
                $currentUser->addHomework($homework);
            }

            $this->getEntityManager()->flush();

            return $this->redirectToRoute('account_index');
        }

        $form = $this->createForm(ChildType::class, $currentUser);
        $form->remove('isTeacher');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getEntityManager()->flush();

            return $this->redirectToRoute('teacher_appoint', [
                'id' => $teacher->getId(),
            ]);
        }

        if (!$form->isSubmitted()) {
            foreach ($errors as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        $this->missResponseEvent();

        return $this->render('teacher/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/disappoint", name="teacher_disappoint")
     */
    public function disappoint(): Response
    {
        $this->denyAccessUnlessGranted('DISAPPOINT_TEACHERS');
        $this->currentUser->setTeacher(null);
        $this->getEntityManager()->flush();

        return $this->redirectToRoute('account_index');
    }
}
