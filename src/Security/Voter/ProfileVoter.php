<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Entity\Profile;
use App\Service\UserLoader;

class ProfileVoter extends Voter
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
        return             !is_array($subject) ? ($subject instanceof Profile or null === $subject && $this->hasHandler($attribute)) : $this->supportsArr($attribute, $subject);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (is_array($subject)) {
            return $this->voteOnArr($attribute, $subject, $token);
        }

        if ($this->ch->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        return $this->checkRight($attribute, $subject, $token);
    }

    private function canCreateProfile()
    {
        return $this->canCreate();
    }

    private function canCreate()
    {
        return !$this->ul->isGuest();
    }

    private function canView()
    {
        $p = $this->subj;
        $u = $this->ul->getUser();
        $au = $p->getAuthor();

        return $p->isPublic() or $u === $au or $u->isUserTeacher($au);
    }

    private function canEdit($p)
    {
        return $this->canCreate() && $this->ul->getUser() === $p->getAuthor() && !$p->isPublic();
    }

    private function canDelete($p)
    {
        return $this->canEdit($p);
    }

    private function canAppoint($p)
    {
        return $this->ch->isGranted('IS_ACCOUNT_PAID') && $this->canCreate($p) && $this->canView($p) && $this->ul->getUser()->getCurrentProfile() !== $this->subj;
    }

    private function canCopy()
    {
        return $this->canCreate() && $this->canView();
    }
}
