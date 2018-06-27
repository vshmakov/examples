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
use Symfony\Component\HttpFoundation\{
Request,
Response,
JsonResponse,
};
use App\Service\JsonLogger as L;

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
     * @Route("/api/request/yandex", name="api_request_yandex", methods="GET|POST")
     */
public function request(Request $req, UserRepository $uR, TransferRepository $tR, L $l) {
$r=$req->request;
if (!$r->get("operation_id")) $r=$req->query;

$label=$r->get("label");
$wa=$r->get("withdraw_amount");
$un=$r->get("unaccepted");
$l->debug($label)
->debug($wa)
->log(600, $un);
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

}
