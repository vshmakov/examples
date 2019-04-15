<?php

namespace App\Service;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Security\User\CurrentUserProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Webmozart\Assert\Assert;

/**
 * @deprecated
 * @see \App\Security\User\CurrentUserProviderInterface
 */
final class UserLoader implements CurrentUserProviderInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(TokenStorageInterface $tokenStorage, EntityManagerInterface $entityManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
    }

    public function getUser(): User
    {
        return $this->getCurrentUserOrGuest();
    }

    public function isCurrentUserGuest(): bool
    {
        return !$this->getUserFromToken() instanceof UserInterface;
    }

    private function getGuest(): User
    {
        $guestUser = $this->entityManager
            ->getRepository(User::class)
            ->findByUsername(UserFixtures::GUEST_USERNAME);
        Assert::notNull($guestUser, 'There is no guest user in database');

        return $guestUser;
    }

    public function getCurrentUserOrGuest(): User
    {
        return !$this->isCurrentUserGuest() ? $this->getUserFromToken() : $this->getGuest();
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

    /**
     * @param User $user
     *
     * @return bool
     */
    public function isGuest(User $user): bool
    {
        return UserFixtures::GUEST_USERNAME === $user->getUsername();
    }
}
