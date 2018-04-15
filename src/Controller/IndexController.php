<?php

namespace App\Controller;

use App\Repository\SessionRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class IndexController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(SessionRepository $sR)
    {
$sR->findByCurrentUserOrGetNew();
        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }
}
