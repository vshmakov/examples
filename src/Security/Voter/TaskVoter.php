<?php

namespace App\Security\Voter;

use App\Entity\Task;
use App\Service\AuthChecker;
use App\Service\UserLoader;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TaskVoter extends Voter
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
        return $subject instanceof Task && $this->hasHandler($attribute);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->voteOnNamedCallback($attribute, $subject, $token);
    }

    private function canShow(): bool
    {
        return $this->subject->isAuthor($this->userLoader->getUser());
    }

    private function canEdit(): bool
    {
        return $this->canShow();
    }

    private function canDelete(): bool
    {
        return $this->canShow();
    }
}
