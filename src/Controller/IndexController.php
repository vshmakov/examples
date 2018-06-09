<?php

namespace App\Controller;

use App\Repository\SessionRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Repository\ProfileRepository as PR;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(\App\Service\UserLoader $ul, \App\Repository\UserRepository $uR, \Symfony\Component\DependencyInjection\ContainerInterface $con)
    {
//dump($con);
        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }

    /**
     * @Route("/api/request/yandex", name="api_request_yandex", methods="POST")
     */
public function request(Request $req, UserRepository $uR) {
$r=$req->request;
$code=400;
$u=preg_match("#^".RECHARGE_TITLE."(.+)$#u", $r->get("label"), $arr) ? $uR->findOneByEmail($arr[1]) : null;
$un=$r->get("unaccepted");

if ($u && $op=$r->get("operation_id") && $un != "true") {
$wa=$r->get("withdraw_amount");
$u->addMoney($wa);
$this->em()->flush();
$code=200;
}

return new Response("", $code);
}

}
