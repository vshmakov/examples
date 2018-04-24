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
class AccountController extends Controller
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
$this->getDoctrine()->getManager()->flush();
}

return $this->render("account/Recharge.html.twig", [
"u"=>$this->u,
"form"=>$form->createView(),
"m"=>$m ?: "",
]);
}
}
