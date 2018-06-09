<?php

namespace App\Controller;

use App\DT;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\UserLoader;

/**
 * @Route("/account")
 */
class AccountController extends MainController
{
private $u;
private $t="Пополнение счёта exmasters.ru пользователя ";

public function __construct(UserRepository $uR, UserLoader $ul) {
$this->u=$ul->getUser()->setER($uR);
}

/**
*@Route("/recharge", name="account_recharge")
*/
public function recharge() {
return $this->render("account/Recharge.html.twig", [
"t"=>$this->t,
]);
}

    /**
     * @Route("/request", name="account_request", methods="POST")
     */
public function request(Request $req) {
$r=$req->request;
$code=400;
$u=preg_match("#^".$this->t."(.+)$#u", $r->get(""), $arr) ? $uR->findOneByEmail($arr[1]) : null;
$un=$r->get("unaccepted");

if ($u && $op=$r->get("operation_id") && $un != "true") {
$wa=$r->get("withdraw_amount");
$u->addMoney($wa);
$this->em()->flush();
$code=200;
}

return new Response("", $code);
}

/**
*@Route("/pay", name="account_pay", methods="GET|POST")
*/
public function pay(Request $r) {
$m=(int) $r->request->get("months");
$u=$this->u;
$remMon=$u->getMoney() - $m*PRICE;

if ($m && $remMon >= 0) {
$f=$u->getLimitTime();
if ($f->isPast()) $f=new DT();
$u->setLimitTime($f->add(new \DateInterval("P{$m}M")))
->setMoney($remMon);
$this->em()->flush();
$m=0;
}

return $this->render("account/pay.html.twig", [
"m"=>$m ?: "",
"price"=>PRICE,
]);
}
}
