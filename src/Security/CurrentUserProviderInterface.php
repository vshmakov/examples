<?php

namespace App\Security;

use App\Entity\User;

interface CurrentUserProviderInterface
{
    public function getCurrentUserOrGuest(): User;

    public function isCurrentUser(User $user): bool;
}
