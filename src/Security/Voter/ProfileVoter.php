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
private $p;
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
$this->p=$subject;

        switch ($attribute) {
case "DELETE":
return $this->canDelete();
break;
case "CREATE":
return $this->canCreate();
break;
            case 'EDIT':
return $this->canEdit();
                break;
case"APPOINT":
return $this->canAppoint();
break;
            case 'VIEW':
return $this->canView();
                break;
        }

        return false;
    }

private function canCreate() {
return !$this->ul->isGuest();
}

private function canView() {
$p=$this->p;
return $p->isPublic() or $this->ul->getUser() === $p->getAuthor();
}

private function canEdit() {
return $this->canCreate() && $this->ul->getUser() === $this->p->getAuthor() && !$this->p->isPublic();
}

private function canDelete() {
return $this->canEdit();
} 

private function canAppoint() {
return $this->canCreate() && $this->canView();
} 
}
