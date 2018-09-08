<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Entity\User;
use App\Service\UserLoader;

class TeacherVoter extends Voter
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
        return             $subject instanceof User && $subject->isTeacher() && $this->hasHandler($attribute);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        //if ($this->ch->isGranted("ROLE_SUPER_ADMIN")) return true;
        return $this->checkRight($attribute, $subject, $token);
    }

    private function canAppoint()
    {
        $t = $this->subj;

        return !$this->ul->isGuest() && $t->isTeacher() && !$this->ul->getUser()->isUserTeacher($t);
    }

    private function canDisappoint()
    {
        return $this->ul->getUser()->isUserTeacher($this->subj);
    }
}