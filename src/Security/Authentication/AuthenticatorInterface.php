<?php

namespace App\Security\Authentication;

use App\Entity\User;

interface AuthenticatorInterface
{
    public function authenticate(User $user): void;
}
