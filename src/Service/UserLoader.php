<?php

namespace App\Service;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\UserRepository;

class UserLoader {
const GUEST_LOGIN="__guest";
private $user;
private $ur;

public function __construct(UserRepository $ur, TokenInterface $token=null) {
$this->ur=$ur;
//        $this->user = $token->getUser();
}

public function getUser() {
return ($this->user instanceof UserInterface) ? $this->user : $this->getGuest();
}

private function getGuest() {
return null;
}
}