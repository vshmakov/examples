<?php

namespace App\User;

use App\Entity\User;
use App\Entity\User\SocialAccount;

interface UserProviderInterface
{
    public function getOrCreateUser(SocialAccount $socialAccount): User;
}
