<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Entity\User;
use App\Service\UserLoader;

class StudentVoter extends Voter
{
    use BaseTrait;
    private $ul;
    private $ch;

    public function __construct(UserLoader $ul, AuthorizationCheckerInterface $ch)
    {
        $this->ul = $ul;
        $this->ch = $ch;
    }

    protected function supports($attribute, $subject)
    {
        return             $subject instanceof User && $subject->isStudent() && $this->hasHandler($attribute);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        //if ($this->ch->isGranted("ROLE_SUPER_ADMIN")) return true;
        return $this->checkRight($attribute, $subject, $token);
    }

    private function canShowAttempts()
    {
        return $this->subj->isUserTeacher($this->ul->getUser());
    }

    private function canShowExamples()
    {
        return $this->canShowAttempts();
    }
}