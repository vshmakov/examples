<?php

namespace App\Security\Voter;

use App\Security\Voter\Traits\BaseTrait;
use App\Service\AuthChecker;
use App\Service\UserLoader;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ChildVoter extends Voter
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
        return $this->supportsUser($attribute, $subject)
            && (null === $subject || $subject->hasParent());
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->voteOnNamedCallback($attribute, $subject, $token);
    }

    private function canLoginAsChild()
    {
        return $this->subject->isParent($this->userLoader->getUser());
    }

    private function canEditChild()
    {
        return $this->canLoginAsChild();
    }

    private function canCreateChild()
    {
        $authChecker = $this->authChecker;

        return $authChecker->isGranted('ROLE_USER') && !$authChecker->isGranted('ROLE_CHILD')
            && $this->userLoader->getUser()->isTeacher();
    }
}
