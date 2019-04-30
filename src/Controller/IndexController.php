<?php

namespace App\Controller;

use App\ApiPlatform\Attribute;
use App\ApiPlatform\Filter\Validation\FilterUserValidationSubscriber;
use App\ApiPlatform\Format;
use App\Attempt\EventSubscriber\ShowAttemptsCollectionSubscriber;
use App\Controller\Traits\CurrentUserProviderTrait;
use App\Controller\Traits\JavascriptParametersTrait;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class IndexController extends Controller
{
    use CurrentUserProviderTrait, JavascriptParametersTrait;

    /**
     * @Route("/", name="homepage", methods={"GET"})
     */
    public function guestHomepage(): Response
    {
        return $this->render('index/homepage/guest.html.twig');
    }

    public function studentHomepage(): Response
    {
        $this->setJavascriptParameters([
            'getAttemptsUrl' => $this->generateUrl(ShowAttemptsCollectionSubscriber::ROUTE, [FilterUserValidationSubscriber::FIELD => $this->getCurrentUserOrGuest()->getUsername(), Attribute::FORMAT => Format::JSONDT]),
        ]);

        return $this->render('index/homepage/student.html.twig', [
        ]);
    }

    public function teacherHomepage(): Response
    {
        return $this->render('index/homepage/teacher.html.twig');
    }
}
