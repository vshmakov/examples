<?php

namespace App\Controller;

use App\Form\AccountType;
use App\Repository\TransferRepository;
use App\Repository\UserRepository;
use App\Service\UserLoader;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/account")
 */
class AccountController extends Controller
{
    use BaseTrait;
    private $currentUser;

    public function __construct(UserRepository $userRepository, UserLoader $userLoader)
    {
        $this->currentUser = $userLoader->getUser()
            ->setEntityRepository($userRepository);
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
    public function recharge(TransferRepository $transferRepository)
    {
        return $this->render('account/Recharge.html.twig', [
            'label' => $transferRepository->findUnheldByCurrentUserOrNew()->getLabel(),
        ]);
    }

    /**
     *@Route("/pay", name="account_pay", methods="GET|POST")
     */
    public function pay(Request $request)
    {
        $month = (int) $request->request->get('months');
        $user = $this->currentUser;
        $remaindMoney = $user->getMoney() - $month * PRICE;

        if ($month && $remaindMoney >= 0) {
            $limitTime = $user->getLimitTime();

            if ($limitTime->isPast()) {
                $limitTime = new \DT();
            }
            $user->setLimitTime($limitTime->add(new \DateInterval("P{$month}M")))
                ->setMoney($remaindMoney);
            $this->getEntityManager()->flush();
            $month = 0;
        }

        return $this->render('account/pay.html.twig', [
            'm' => $month ?: '',
            'price' => PRICE,
        ]);
    }

    /**
     *@Route("/edit", name="account_edit", methods="GET|POST")
     */
    public function edit(Request $request, SessionInterface $session)
    {
        $this->denyAccessUnlessGranted('EDIT_ACCOUNT');
        $user = $this->currentUser;
        $user->cleanSocialUsername();
        $form = $this->createForm(AccountType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getEntityManager()->flush();

            return $this->redirectToRoute('account_index');
        }

        $this->missResponseEvent();

        return $this->render('account/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
