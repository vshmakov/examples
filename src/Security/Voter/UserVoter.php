<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Security\Voter\Traits\BaseTrait;
use App\Service\AuthChecker;
use App\Service\UserLoader;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    use BaseTrait;
    private $userLoader;
    private $authChecker;

    public function __construct(UserLoader $userLoader, AuthChecker $authChecker)
    {
        $this->userLoader = $userLoader;
        $this->authChecker = $authChecker;
    }

    protected function supports($attribute, $subject)
    {
        return ($subject instanceof User or null === $subject) && $this->hasHandler($attribute);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->voteOnNamedCallback($attribute, $subject ?? $this->userLoader->getUser(), $token);
    }

    private function isAccountPaid()
    {
        return !$this->userLoader->isCurrentUserGuest();
    }

    private function hasPrivAppointProfiles()
    {
        return $this->authChecker->isGranted('ROLE_USER', $this->subject);
    }

    private function canCreateChildren()
    {
        return !$this->authChecker->isGranted('ROLE_CHILD');
    }

    private function canLogin()
    {
        return $this->authChecker->isGranted('ROLE_SUPER_ADMIN');
    }

    private function canShowTasks(): bool
    {
        $authChecker = $this->authChecker;

        return $authChecker->isGranted('ROLE_USER') && !$authChecker->isGranted('ROLE_CHILD')
            && $this->userLoader->getUser()->isTeacher();
    }

    private function canShowHomeworks(): bool
    {
        $currentUser = $this->userLoader->getUser();

        return $this->authChecker->isGranted('ROLE_USER')
            && (!$currentUser->isTeacher() or $currentUser->getHomework()->count());
    }

    private function canCreateTasks(): bool
    {
        return $this->canShowTasks();
    }
}
