<?php

declare(strict_types=1);

namespace App\Controller;

use App\Security\User\CurrentUserProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class IndexController extends Controller
{
    /**
     * @Route("/", name="homepage", methods={"GET"})
     */
    public function index(SessionInterface $session, TokenStorageInterface $tokenStorage, CurrentUserProviderInterface $currentUserProvider): Response
    {
        return $this->render('index/index.html.twig');
    }
}
