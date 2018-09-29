<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class IndexController extends Controller
{
    use BaseTrait;

    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
        return $this->render('index/index.html.twig');
    }
}
