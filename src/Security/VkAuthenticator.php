<?php

namespace App\Security;

use   Psr\Container\ContainerInterface as Con;
use Symfony\Component\HttpFoundation\{
Request,
RedirectResponse,
};
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use App\Repository\{
UserRepository as UR,
};

class VkAuthenticator extends AbstractGuardAuthenticator
{
private $con;
private $uR;

public function __construct(Con $con, UR $uR)
{
$this->con=$con;
$this->uR=$uR;
}

    public function supports(Request $request)
    {
return $request->attributes->get("_route") == "api_login_vk";
    }

    public function getCredentials(Request $request)
    {
$d=[];
foreach (getArrByStr("uid first_name last_name photo photo_rec hash") as $k) {
$d[$k]=$request->query->get($k);
}
return $d;
    }

    public function getUser($d, UserProviderInterface $p)
    {
return $p->loadUserByUsername($d["uid"]);
    }

    public function checkCredentials($credentials, UserInterface $user=null)
    {
extract($credentials);
$appId=$this->con->getParameter("vk_app_id");
$secretKey="2jZQwVIL7krfQ7f9GSZS";
$k=md5($appId.$uid.$secretKey);
return $hash == $k;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
return new RedirectResponse("/login");
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
return new RedirectResponse("/");
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