<?php

namespace App\Controller;

use App\DT;
use App\Repository\UserRepository;
use App\Repository\TransferRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\UserLoader;

/**
 * @Route("/account")
 */
class AccountController extends MainController
{
    private $u;

    public function __construct(UserRepository $uR, UserLoader $ul)
    {
        $this->u = $ul->getUser()->setER($uR);
    }

    /**
     *@Route("/recharge", name="account_recharge")
     */
    public function recharge(TransferRepository $tR)
    {
        return $this->render('account/Recharge.html.twig', [
            't' => RECHARGE_TITLE,
            'label' => $tR->findUnheldByCurrentUserOrNew()->getLabel(),
        ]);
    }

    /**
     *@Route("/pay", name="account_pay", methods="GET|POST")
     */
    public function pay(Request $r)
    {
        $m = (int) $r->request->get('months');
        $u = $this->u;
        $remMon = $u->getMoney() - $m * PRICE;

        if ($m && $remMon >= 0) {
            $f = $u->getLimitTime();

            if ($f->isPast()) {
                $f = new DT();
            }
            $u->setLimitTime($f->add(new \DateInterval("P{$m}M")))
->setMoney($remMon);
            $this->em()->flush();
            $m = 0;
        }

        return $this->render('account/pay.html.twig', [
            'm' => $m ?: '',
            'price' => PRICE,
        ]);
    }
}
