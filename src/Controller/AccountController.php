<?php

namespace App\Controller;

use App\Controller\Traits\CurrentUserProviderTrait;
use App\Entity\User\Role;
use App\Form\AccountType;
use App\Security\Authentication\AuthenticatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/account")
 * @IsGranted(Role::USER)
 */
final class AccountController extends Controller
{
    use  CurrentUserProviderTrait;

    /**
     * @Route("/", name="account_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('account/index.html.twig');
    }

    /**
     * @Route("/edit/", name="account_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, SessionInterface $session, AuthenticatorInterface $authenticator): Response
    {
        $currentUser = $this->getCurrentUserOrGuest();
        $currentUser->cleanSocialUsername();
        $form = $this->createForm(AccountType::class, $currentUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()
                ->getManager()
                ->flush($currentUser);

            $authenticator->authenticate($currentUser);

            return $this->redirectToRoute('account_index');
        }

        return $this->render('account/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
