<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use App\Repository\UserRepository;

class UloginAuthenticator extends AbstractGuardAuthenticator
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function supports(Request $request)
    {
        return 'api_ulogin_login' == $request->attributes->get('_route');
    }

    public function getCredentials(Request $request)
    {
        try {
            $token = $request->request->get(
                'token',
                $request->query->get('token')
            );
            $json = file_get_contents(sprintf(
                'http://ulogin.ru/token.php?token=%s&host=%s',
                $token,
                $request->server->get('HTTP_HOST')
            ));

            $credentials = json_decode($json, true);
            $credentials += [
                'token' => $token,
                'username' => '^' . $credentials['network'] . '-' . $credentials['uid'],
            ];

            return $credentials;
        } catch (\Exception $exception) {
            return [];
        }
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $credentials ? $this->userRepository->findOneByUloginCredentials($credentials) : null;
    }

    public function checkCredentials($credentials, UserInterface $user = null)
    {
        return (bool)$credentials;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new RedirectResponse('/login');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return new RedirectResponse('/');
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        // todo
    }

    public function supportsRememberMe()
    {
        return true;
    }
}
