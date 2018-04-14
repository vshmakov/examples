<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface as EM;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\UserRepository;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserLoader {
const GUEST_LOGIN="__guest";
private $user;
private $uR;
private $em;

public function __construct(UserRepository $uR, EM $em, TokenStorageInterface $ts) {
$this->uR=$uR;
$this->em=$em;
        $this->user = $ts->getToken()->getUser();
}

public function getUser() {
return (!$this->isGuest()) ? $this->user : $this->getGuest();
}

public function isGuest() {
return !($this->user instanceof UserInterface);
}

private function getGuest() {
static $u=false;
$gl=self::GUEST_LOGIN;
if ($u===false) $u=$this->uR->findOneByUsername($gl);
if (!$u) {
$u=new User();
$u->setUsername($gl)
->setUsernameCanonical($gl)
->setEmail('')
->setEmailCanonical('')
->setPassword('')
->setEnabled(true);

$em=$this->em;
$em->persist($u);
$em->flush();
}
return $u;
}
}