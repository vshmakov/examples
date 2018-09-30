<?php

namespace App\Security\Voter;

use App\Service\AuthChecker;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Entity\User;
use App\Service\UserLoader;

class StudentVoter extends Voter
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
        return             $subject instanceof User && $subject->isStudent() && $this->hasHandler($attribute);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
                return $this->checkRight($attribute, $subject, $token);
    }

    private function canShowAttempts()
    {
        return $this->subject->isUserTeacher($this->userLoader->getUser());
    }

    private function canShowExamples()
    {
        return $this->canShowAttempts();
    }
}
