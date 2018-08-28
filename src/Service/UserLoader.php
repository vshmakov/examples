<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface as EM;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserLoader
{
    private $user;
    private $uR;
    private $em;

    public function __construct(UserRepository $uR, EM $em, TokenStorageInterface $ts)
    {
        $this->uR = $uR;
        $this->em = $em;

        if ($tk = $ts->getToken()) {
            $this->user = $tk->getUser();
        }
    }

    public function getUser()
    {
        return (!$this->isGuest()) ? $this->user : $this->getGuest();
    }

    public function isGuest()
    {
        return !($this->user instanceof UserInterface);
    }

    public function getGuest()
    {
        return $this->uR->getGuest();
    }
}
