<?php

namespace App\Security\Authentication;

use App\Tests\Functional\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

final class TestAuthenticator extends AbstractGuardAuthenticator
{
    public function supports(Request $request)
    {
        return $request->server->has(BaseWebTestCase::TEST_AUTHENTICATION_HEADER_NAME);
    }

    public function getCredentials(Request $request)
    {
        return [
            'username' => $request->server->get(BaseWebTestCase::TEST_AUTHENTICATION_HEADER_NAME),
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $userProvider->loadUserByUsername($credentials['username']);
    }

    public function checkCredentials($credentials, UserInterface $user = null)
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        throw new \LogicException('Test authentication das not work');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
