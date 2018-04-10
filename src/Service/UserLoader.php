<?php

namespace App\Service;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserLoader {
const GUEST_LOGIN="__guest";
private $user;
private $ur;

public function __construct(UserRepository $ur, TokenStorageInterface $ts) {
$this->ur=$ur;
        $this->user = $ts->getToken()->getUser();
}

public function getUser() {
return ($this->user instanceof UserInterface) ? $this->user : $this->getGuest();
}

private function getGuest() {
return null;
}
}