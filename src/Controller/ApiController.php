<?php

namespace App\Controller;

use App\Security\UloginAuthenticator as UloginAuth;
use   Psr\Container\ContainerInterface as Con;
use App\Repository\TransferRepository;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface as SI;
use App\Service\JsonLogger as L;

/**
 * @Route("/api")
 */
class ApiController extends Controller
{
    use BaseTrait;

    /**
     * @Route("/request/yandex", name="api_request_yandex", methods="POST")
     */
    public function request(Request $req, UserRepository $uR, TransferRepository $tR, L $l)
    {
        $r = $req->request;

        $label = $r->get('label');
        $wa = $r->get('withdraw_amount');
        $un = $r->get('unaccepted');

        $code = 400;
        $an = ['error' => "No transfer with $label label"];
        $t = $tR->findOneBy(['label' => $label, 'held' => false]);
        $u = $t ? $t->getUser() : null;

        if ($u && 'true' != $un) {
            $u->addMoney($wa);
            $t->setMoney($wa)
->setHeldTime(new \DateTime())
->setHeld(true);
            $this->em()->flush();
            $code = 200;
            $an['error'] = false;
        }

        return new JsonResponse($an, $code);
    }

    /**
     * @Route("/login/vk", name="api_login_vk")
     */
    public function vk(Request $req, Con $con)
    {
        dd($req->query->all(), 123);

        return $this->redirectToRoute('fos_user_security_login');
    }

    /**
     * @Route("/register/vk", name="api_register_vk")
     */
    public function registerVk(Request $req, VkAuth $vkAuth = null, UserRepository $uR)
    {
        $d = $vkAuth->getCredentials($req);

        if ($vkAuth->checkCredentials($d)) {
            $uR->findOneByVkCredentialsOrNew($d);
        }

        return $this->redirectToRoute('api_login_vk', $d);
    }

    /**
     * @Route("/ulogin/login", name="api_ulogin_login")
     */
    public function ulogin(Request $req)
    {
        return $this->redirectToRoute('fos_user_security_login');
    }

    /**
     * @Route("/ulogin/register", name="api_ulogin_register", methods="POST")
     */
    public function uloginRegister(Request $req, UloginAuth $uloginAuth, UserRepository $uR, SI $s)
    {
        $d = $uloginAuth->getCredentials($req);

        if ($uloginAuth->checkCredentials($d)) {
            $uR->findOneByUloginCredentialsOrNew($d);
        }

        return $this->redirectToRoute('api_ulogin_login', [
            'token' => $req->request->get('token'),
        ]);
    }
}
