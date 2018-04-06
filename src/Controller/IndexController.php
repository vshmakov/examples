<?php

namespace AppBundle\Controller\Frontend;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\MainController;

class IndexController extends MainController
{
    /**
     @Route("/", name="homepage")
     */
    public function indexAction(Request $request, SessionInterface $session)
    {
return $this->render('frontend/index/index.html.twig', [
'title'=>'Добро пожаловать!',
]);
	}
}