<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\UserLoader;

/**
 * @Route("/account")
 */
class AccountController extends Controller
{
    /**
     * @Route("/", name="account_index", methods="GET")
     */
    public function index(UserRepository $uR, UserLoader $ul): Response
    {
        return $this->render('account/index.html.twig', [
"u"=>$ul->getUser()->setER($uR),
]);
    }
}
