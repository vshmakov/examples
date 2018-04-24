<?php

namespace App\Controller;

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

public function __construct(UserRepository $uR, UserLoader $ul) {
$this->u=$ul->getUser()->setER($uR);
}

    /**
     * @Route("/", name="account_index", methods="GET")
     */
    public function index(): Response
    {
        return $this->render('account/index.html.twig', [
"u"=>$this->u,
]);
    }

/**
*@Route("/Recharge", name="account_Recharge")
*/
public function Recharge(Request $r) {
$form=$this->createFormBuilder($this->u)
->add("money")
->getForm();
$m=(int) $r->request->get("money");

if ($m) {
$u=$this->u;
$u->setMoney($u->getMoney() + $m);
$this->em()->flush();
}

return $this->render("account/Recharge.html.twig", [
"u"=>$this->u,
"form"=>$form->createView(),
"m"=>$m ?: "",
]);
}

/**
*@Route("/pay", name="account_pay")
*/
public function pay(Request $r) {
$m=(int) $r->request->get("months");
$u=$this->u;
$remMon=$u->getMoney() - $m*50;

if ($m && $remMon >= 0) {
$f=$u->getLimitTime();
if ($f->isPast()) $f=new DT();
$u->setLimitTime($f->add(new \DateInterval("P{$m}M")))
->setMoney($remMon);
$this->em()->flush();
}

return $this->render("account/pay.html.twig", [
"m"=>$m ?: "",
"u"=>$this->u,
]);
}
}
