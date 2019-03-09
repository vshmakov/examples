<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\User\CurrentUserProviderInterface;

trait CurrentUserProviderTrait
{
    private function getCurrentUserOrGuest(): User
    {
        return $this->container
            ->get(CurrentUserProviderInterface::class)
            ->getCurrentUserOrGuest();
    }
}
