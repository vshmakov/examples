<?php

namespace App\Security\Authentication\Guard;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Webmozart\Assert\Assert;

final class LoginAuthenticator extends AbstractGuardAuthenticator
{
    public const  LOGIN_AS_USER = 'login_as_user';
    public const  REDIRECT_AFTER_LOGIN = 'redirect-url';

    public function supports(Request $request): bool
    {
        return $request->hasSession() && $request->getSession()->getFlashBag()->has(self::LOGIN_AS_USER);
    }

    public function getCredentials(Request $request): array
    {
        return [
            'userId' => $request->getSession()->getFlashBag()->get(self::LOGIN_AS_USER)[0] ?? null,
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider): User
    {
        return $userProvider->loadUserByUsername($credentials['userId']);
    }

    public function checkCredentials($credentials, UserInterface $user = null): bool
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): void
    {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): RedirectResponse
    {
        $redirectUrl = $request->query->get(self::REDIRECT_AFTER_LOGIN, '/');
        Assert::regex($redirectUrl, '#^/#');

        return new RedirectResponse($redirectUrl);
    }

    public function start(Request $request, AuthenticationException $authenticationException = null): void
    {
    }

    public function supportsRememberMe(): bool
    {
        return true;
    }
}
