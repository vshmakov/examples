<?php

namespace App\Controller;

use App\Form\StudentType;
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
     *@Route("/new", name="child_new")
     */
    public function new(Request $request, UserRepository $userRepository)
    {
        $currentUser = $this->currentUser;
        $child = (new User)
            ->setParent($currentUser);

        $i = 0;
        do {
            $i++;
            $username = sprintf('%s-%s', $currentUser->getUsername(), $i);
        } while ($userRepository->countByUsername($username));

        $child->setUsername($username);
        $form = $this->createForm(StudentType::class, $child, ['validation_groups' => ['Default']])
            ->remove('fatherName');
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
        $this->denyAccessUnlessGranted('CHILD_LOGIN', $child);
        $this->addFlash('login', true);

        return $this->redirectToRoute('api_login', [
            'id' => $child->getId()
        ]);
    }
}
