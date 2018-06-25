<?php

namespace App\Controller;

use App\Repository\{
SessionRepository,
TransferRepository,
ProfileRepository as PR,
UserRepository,
};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
public function request(Request $req, UserRepository $uR, TransferRepository $tR) {
$r=$req->request;
$code=400;
$t=$tR->findOneBy(["label"=>$r->get("label"), "held"=>false]);
$u= $t ? $t->getUser() : null;
$un=$r->get("unaccepted");

if ($u && $un != "true") {
$wa=$r->get("withdraw_amount");
$u->addMoney($wa);
$t->setMoney($wa)
->setHeldTime(new \DateTime)
->setHeld(true);
$this->em()->flush();
$code=200;
}

return new Response("", $code);
}

}
