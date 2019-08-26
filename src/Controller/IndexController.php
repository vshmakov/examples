<?php

namespace App\Controller;

use App\ApiPlatform\Attribute;
use App\ApiPlatform\Filter\Validation\FilterUserValidationSubscriber;
use App\ApiPlatform\Format;
use App\Attempt\EventSubscriber\ShowAttemptsCollectionSubscriber;
use App\Controller\Traits\CurrentUserProviderTrait;
use App\Controller\Traits\JavascriptParametersTrait;
use App\Entity\User\Role;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class IndexController extends Controller
{
    use CurrentUserProviderTrait;
    use JavascriptParametersTrait;

    /**
     * @Route("/", name="homepage", methods={"GET"})
     */
    public function homepage(AuthorizationCheckerInterface $authorizationChecker): Response
    {
        return $this->render($this->getHomepageTemplate($authorizationChecker));
    }

    private function getHomepageTemplate(AuthorizationCheckerInterface $authorizationChecker): string
    {
        if ($authorizationChecker->isGranted(Role::STUDENT)) {
            $this->setJavascriptParameters([
                'getAttemptsUrl' => $this->generateUrl(ShowAttemptsCollectionSubscriber::ROUTE, [FilterUserValidationSubscriber::FIELD => $this->getCurrentUserOrGuest()->getUsername(), Attribute::FORMAT => Format::JSONDT]),
            ]);

            return 'index/homepage/student.html.twig';
        }

        if ($authorizationChecker->isGranted(Role::TEACHER)) {
            return 'index/homepage/teacher.html.twig';
        }

        return 'index/homepage/guest.html.twig';
    }

    /**
     * @Route("/help/", name="help", methods={"GET"})
     */
    public function help(): Response
    {
        return $this->render('index/help.html.twig');
    }

    /**
     * @Route("/contacts/", name="contacts", methods={"GET"})
     */
    public function contacts(): Response
    {
        return $this->render('index/contacts.html.twig');
    }
}
