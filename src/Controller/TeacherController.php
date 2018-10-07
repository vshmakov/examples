<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Form\AccountType;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\UserLoader;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/teacher")
 * @Security("is_granted('SHOW_TEACHERS')")
 */
class TeacherController extends Controller
{
    use BaseTrait;
    private $currentUser;

    public function __construct(UserRepository $userRepository, UserLoader $userLoader)
    {
        $this->currentUser = $userLoader->getUser()
            ->setEntityRepository($userRepository);
    }

    /**
     *@Route("/", name="teacher_index")
     */
    public function index(UserRepository $userRepository)
    {
        return $this->render('teacher/index.html.twig', [
            'teachers' => $userRepository->findByIsTeacher(true),
        ]);
    }

    /**
     *@Route("/{id}/appoint", name="teacher_appoint")
     */
    public function appoint(User $teacher, ValidatorInterface $validator, Request $request, SessionInterface $session)
    {
        $this->denyAccessUnlessGranted('APPOINT_TEACHER', $teacher);
        $currentUser = $this->currentUser;
        $errors = $validator->validate($currentUser, null, ['account']);

        if (!count($errors)) {
            if ($this->isGranted('APPOINT', $teacher)) {
                $currentUser->setTeacher($teacher);
                $this->getEntityManager()->flush();
            }

            return $this->redirectToRoute('account_index');
        }

        $currentUser->cleanSocialUsername();
        $form = $this->createForm(AccountType::class, $currentUser);
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
     *@Route("/disappoint", name="teacher_disappoint")
     */
    public function disappoint()
    {
        $this->denyAccessUnlessGranted('DISAPPOINT_TEACHER');
        $this->currentUser->setTeacher(null);
        $this->getEntityManager()->flush();

        return $this->redirectToRoute('account_index');
    }
}
