<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\User\CurrentUserProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

final class IndexController extends Controller
{
    /**
     * @Route("/", name="homepage", methods={"GET"})
     */
    public function index(SessionInterface $session, TokenStorageInterface $tokenStorage, CurrentUserProviderInterface $currentUserProvider): Response
    {
        /* @var User $user */
        /*

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneByUsername('teacher');
        dd($user->getRoles());
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $tokenStorage->setToken($token);
        $session->set('_security_main', serialize($token));
        return $this->redirect('/test/');
        */
        return $this->render('index/index.html.twig');
    }

    /**
     * @Route("/test/")
     */
    public function test(SessionInterface $session, TokenStorageInterface $tokenStorage)
    {
        dump($this->getUser(), $session->all(), $tokenStorage);

        return new Response(null);
    }
}
