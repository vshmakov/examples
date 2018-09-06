<?php

namespace App\Controller;

use App\DT;
use App\Repository\UserRepository;
use App\Repository\TransferRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\UserLoader;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

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
     *@Route("/", name="account_index", methods="GET")
     */
    public function index()
    {
        return $this->render('account/index.html.twig');
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

    /**
     *@Route("/edit", name="account_edit", methods="GET|POST")
     */
    public function edit(Request $request)
    {
        $u = $this->u;

        if (preg_match('#^\^#', $u->getUsername())) {
            $u->setUsername('');
        }
        $form = $this->createFormBuilder($u)
->add('username', null, ['label' => 'Логин'])
->add('firstName', null, ['label' => 'Имя'])
->add('fatherName', null, ['label' => 'Отчество'])
->add('lastName', null, ['label' => 'Фамилия'])
->add('isTeacher', CheckboxType::class, ['label' => 'Учитель', 'required' => false])
->add('save', SubmitType::class, ['label' => 'Сохранить'])
->getForm()
;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em()->flush();

            return $this->redirectToRoute('account_index');
        }

        return $this->render('account/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
