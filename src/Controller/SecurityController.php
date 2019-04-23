<?php

namespace App\Controller;

use App\User\SocialAccount\SocialAccountProviderInterface;
use App\User\UserProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/security")
 */
final class SecurityController extends Controller
{
    /**
     * @Route("/ulogin/register/", name="security_ulogin_register", methods={"POST"})
     */
    public function uloginRegister(Request $request, SocialAccountProviderInterface $socialAccountProvider, UserProviderInterface $userProvider): RedirectResponse
    {
        $token = $request->request->get('token');

        if (null === $token) {
            throw new BadRequestHttpException();
        }

        $socialAccount = $socialAccountProvider->getSocialAccount($token);

        if (null === $socialAccount) {
            throw new BadRequestHttpException();
        }

        $user = $userProvider->getOrCreateUser($socialAccount);
        $this->addFlash('login', (string) $user->getId());

        return $this->redirectToRoute('security_login');
    }

    /**
     * @Route("/login/", name="security_login", methods={"GET"})
     */
    public function login(): void
    {
        throw new BadRequestHttpException();
    }
}
