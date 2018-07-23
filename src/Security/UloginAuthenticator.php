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

class UloginAuthenticator extends AbstractGuardAuthenticator
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
return ($request->attributes->get("_route") == "api_ulogin_login");
    }

    public function getCredentials(Request $request)
    {
//                    $s = file_get_contents('http://ulogin.ru/token.php?token=' . $_POST['token'] . '&host=' . $_SERVER['HTTP_HOST']);
$r=$request->request;
if ($token = $r->get('token')) {
$s=file_get_contents('http://ulogin.ru/token.php?token=' . $token . '&host=' . $_SERVER['HTTP_HOST']);
                    $d= json_decode($s, true);
}else {
$d=[];
foreach (getArrByStr("uid first_name last_name network") as $k) {
$d[$k]=$request->query->get($k);
}
}

$d["username"]=$d["network"].$d["uid"];
return $d;
    }

    public function getUser($d, UserProviderInterface $p)
    {
return ($p->loadUserByUsername($d["username"]));
    }

    public function checkCredentials($d, UserInterface $user=null)
    {
return !!$d["username"];
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