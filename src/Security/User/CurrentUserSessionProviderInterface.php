<?php

namespace App\Security\User;

use App\Entity\Session;

interface CurrentUserSessionProviderInterface
{
    public function getCurrentUserSession(): ?Session;

    public function getCurrentUserSessionOrNew(): Session;

    public function isCurrentUserSession(Session $session): bool;
}
