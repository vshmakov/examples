<?php

namespace App\Security\User;

use App\Entity\User;

interface CurrentUserProviderInterface
{
    public function getCurrentUserOrGuest(): User;

    public function isCurrentUser(User $user): bool;

    public function isCurrentUserGuest(): bool;

    public function isGuest(User $user): bool;
}
