<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeworkController extends AbstractController
{
    /**
     * @Route("/homework", name="homework")
     */
    public function index()
    {
        return $this->render('homework/index.html.twig', [
            'controller_name' => 'HomeworkController',
        ]);
    }
}
