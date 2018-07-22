<?php

namespace App\Controller;

use   Psr\Container\ContainerInterface as Con;
use App\Repository\{
SessionRepository,
TransferRepository,
ProfileRepository as PR,
UserRepository,
};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\{
Request,
Response,
JsonResponse,
};
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
public function request(Request $req, UserRepository $uR, TransferRepository $tR, L $l) {
$r=$req->request;

$label=$r->get("label");
$wa=$r->get("withdraw_amount");
$un=$r->get("unaccepted");

$code=400;
$an=["error"=>"No transfer with $label label"];
$t=$tR->findOneBy(["label"=>$label, "held"=>false]);
$u= $t ? $t->getUser() : null;

if ($u && $un != "true") {
$u->addMoney($wa);
$t->setMoney($wa)
->setHeldTime(new \DateTime)
->setHeld(true);
$this->em()->flush();
$code=200;
$an["error"]=false;
}

return new JsonResponse($an, $code);
}

    /**
     * @Route("/login/vk", name="api_login_vk")
     */
public function vk(Request $req, Con $con) {
$r=$req->query;
$appId=$con->getParameter("vk_app_id");
$secretKey="2jZQwVIL7krfQ7f9GSZS";
$uId=$r->get("uid");
$hash=$r->get("hash");
$k=md5($appId.$uId.$secretKey);
dump($appId, $uId, $k, $hash, $k==$hash);
//uid, first_name, last_name, photo, photo_rec, hash 

//return new Response("");
return $this->redirectToRoute("fos_user_security_login");
}
}
