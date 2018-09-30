<?php

namespace App\Service;

use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserLoader
{
    private $user;
    private $userRepository;
    private $em;
    public function __construct(UserRepository $userRepository, TokenStorageInterface $tokenStorage)
    {
        $this->userRepository = $userRepository;

        try {
            $this->user = $tokenStorage->getToken()->getUser();
        } catch (\Exception $exception) {
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
        return $this->userRepository->getGuest();
    }
}
