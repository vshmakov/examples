<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Entity\User;
use App\Service\UserLoader;

class UserVoter extends Voter
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
        return             ($subject instanceof User or null === $subject) && $this->hasHandler($attribute);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        //if ($this->ch->isGranted("ROLE_SUPER_ADMIN")) return true;
        return $this->checkRight($attribute, $subject ?? $this->ul->getUser(), $token);
    }

    private function isAccountPaid()
    {
        $u = $this->subj;

        return !$this->ul->isGuest();
    }

    private function canAppointTeacher()
    {
        $t = $this->subj;

        return !$this->ul->isGuest() && $t->isTeacher() && !$this->ul->getUser()->isUserTeacher($t);
    }

    private function canDisappointTeacher()
    {
        return $this->ul->getUser()->hasTeacher();
    }
}
