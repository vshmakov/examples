<?php

namespace App\Security\Authentication;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

final class LoginAuthenticator extends AbstractGuardAuthenticator
{
    public const  LOGIN_AS_USER = 'login_as_user';

    public function supports(Request $request)
    {
        return $request->hasSession() && $request->getSession()->getFlashBag()->has(self::LOGIN_AS_USER);
    }

    public function getCredentials(Request $request)
    {
        return [
            'userId' => $request->getSession()->getFlashBag()->get(self::LOGIN_AS_USER)[0] ?? null,
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $userProvider->loadUserByUsername($credentials['userId']);
    }

    public function checkCredentials($credentials, UserInterface $user = null)
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new RedirectResponse('/login');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return new RedirectResponse('/');
    }

    public function start(Request $request, AuthenticationException $authenticationException = null)
    {
    }

    public function supportsRememberMe()
    {
        return true;
    }
}
