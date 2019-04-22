<?php

declare(strict_types=1);

namespace App\Security\User;

use App\Entity\Session;
use App\Entity\User;

interface CurrentUserSessionProviderInterface
{
    public function getCurrentUserSession(): ?Session;

    public function getCurrentUserSessionOrNew(): Session;

    public function getUserSessionOrNew(User $user): Session;

    public function isCurrentUserSession(Session $session): bool;
}
