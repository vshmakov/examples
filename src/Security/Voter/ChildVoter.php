<?php

namespace App\Security\Voter;

use App\Service\AuthChecker;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Entity\User;
use App\Service\UserLoader;

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
        return $this->checkRight($attribute, $subject, $token);
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
