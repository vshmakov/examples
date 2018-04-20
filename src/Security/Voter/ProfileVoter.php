<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Profile;
use App\Service\UserLoader;

class ProfileVoter extends Voter
{
use BaseTrait;
private $ul;
private $ch;

public function __construct(UserLoader $ul, AuthorizationCheckerInterface $ch) {
$this->ul=$ul;
$this->ch=$ch;
}

    protected function supports($attribute, $subject)
    {
        return             $subject instanceof Profile;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
if ($this->ch->isGranted("ROLE_SUPER_ADMIN")) return true;
return $this->checkRight($attribute, $subject, $token);
    }

private function canCreate() {
return !$this->ul->isGuest();
}

private function canView($p) {
return $p->isPublic() or $this->ul->getUser() === $p->getAuthor();
}

private function canEdit($p) {
return $this->canCreate() && $this->ul->getUser() === $p->getAuthor() && !$p->isPublic();
}

private function canDelete($p) {
return $this->canEdit($p);
} 

private function canAppoint($p) {
return $this->canCreate($p) && $this->canView($p);
} 
}