<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

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
