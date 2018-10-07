<?php

namespace App\Controller;

use App\Form\ChildType;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\UserLoader;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/child")
 */
class ChildController extends Controller
{
    use BaseTrait;
    private $currentUser;

    public function __construct(UserRepository $userRepository, UserLoader $userLoader)
    {
        $this->currentUser = $userLoader->getUser()
            ->setEntityRepository($userRepository);
    }

    /**
     *@Route("/{id}/edit", name="child_edit")
     */
    public function edit(User $child, Request $request)
    {
        $this->denyAccessUnlessGranted('EDIT_CHILD', $child);
        $form = $this->createForm(ChildType::class, $child);
                $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getEntityManager();
            $entityManager->flush();

            return $this->redirectToRoute("student_index");
        }

        $this->missResponseEvent();
        
        return $this->render("child/edit.html.twig", [
            "form" => $form->createView()
        ]);
    }

    /**
     *@Route("/new", name="child_new")
     */
    public function new(Request $request, UserRepository $userRepository)
    {
        $this->denyAccessUnlessGranted('CREATE_CHILD');
        $currentUser = $this->currentUser;
        $child = $userRepository->getNew()
            ->setParent($currentUser)
            ->setTeacher($currentUser)
            ->addRole('ROLE_CHILD');

        $i = 0;
        do {
            $i++;
            $username = sprintf('%s-%s', $currentUser->getUsername(), $i);
        } while ($userRepository->countByUsername($username));

        $child->setUsername($username);
        $form = $this->createForm(ChildType::class, $child);
                        $form->handleRequest($request);
        $entityManager = $this->getEntityManager();

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($child);
            $entityManager->flush();

            return $this->redirectToRoute("student_index");
        }

        return $this->render("child/new.html.twig", [
            "form" => $form->createView(),
        ]);
    }

    /**
     *@Route("/{id}/login", name="child_login")
     */
    public function login(User $child)
    {
        $this->denyAccessUnlessGranted('LOGIN_AS_CHILD', $child);
        $this->addFlash('login', true);

        return $this->redirectToRoute('api_login', [
            'user_id' => $child->getId()
        ]);
    }
}
