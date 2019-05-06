<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Security\Authentication\Guard\LoginAuthenticator;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class UserController extends CRUDController
{
    public function loginAsAction(User $user): RedirectResponse
    {
        $this->addFlash(LoginAuthenticator::LOGIN_AS_USER, $user->getId());

        return $this->redirectToRoute('security_login');
    }
}
