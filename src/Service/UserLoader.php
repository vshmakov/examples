<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\CurrentUserProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserLoader implements CurrentUserProviderInterface
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

    /**
     * @deprecated
     * use \App\Security\CurrentUserProviderInterface::getCurrentUserOrGuest instead
     */
    public function getUser(): User
    {
        return $this->getCurrentUserOrGuest();
    }

    public function isGuest(): bool
    {
        return !($this->user instanceof UserInterface);
    }

    public function getGuest(): User
    {
        return $this->userRepository->getGuest();
    }

    public function getCurrentUserOrGuest(): User
    {
        return !$this->isGuest() ? $this->user : $this->getGuest();
    }

    public function isCurrentUser(User $user): bool
    {
        return $user === $this->getUser();
    }

    private function getUserFromToken(): ?User
    {
        if ($token = $this->tokenStorage->getToken()) {
            $user = $token->getUser();

            return $user instanceof User ? $user : null;
        }

        return null;
    }
}
