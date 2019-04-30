<?php

namespace App\Controller;

use App\Attempt\AttemptProviderInterface;
use App\Attempt\Profile\ProfileProviderInterface;
use App\Task\Homework\HomeworkProviderInterface;
use App\User\Teacher\TeacherProviderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
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
    public function guestHomepage(): Response
    {
        return $this->render('index/homepage/guest.html.twig');
    }

    public function studentHomepage(TeacherProviderInterface $teacherProvider, HomeworkProviderInterface $homeworkProvider, ProfileProviderInterface $profileProvider): Response
    {
        return $this->render("index/homepage/student.html.twig", [

        ]);
    }

    public function teacherHomepage(): Response
    {
        return $this->render("index/homepage/teacher.html.twig");
    }
}
