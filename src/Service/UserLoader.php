<?php

namespace App\Service;

use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserLoader
{
    private $user;
    private $userRepository;
    private $tokenStorage;

    public function __construct(UserRepository $userRepository, TokenStorageInterface $tokenStorage)
    {
        $this->userRepository = $userRepository;
        $this->tokenStorage = $tokenStorage;
        $this->user = $this->getUserFromToken();
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
        return $this->userRepository->getGuest();
    }

    private function getUserFromToken()
    {
        if ($token = $this->tokenStorage->getToken()) {
            return  $token->getUser();
        }

        return null;
    }
}
