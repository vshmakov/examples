<?php

namespace App\Security\Voter;

use App\Service\AuthChecker;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Entity\Profile;
use App\Service\UserLoader;

class ProfileVoter extends Voter
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
        return !is_array($subject) ? ($subject instanceof Profile or null === $subject && $this->hasHandler($attribute)) : $this->supportsArr($attribute, $subject);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (is_array($subject)) {
            return $this->voteOnArr($attribute, $subject, $token);
        }

        if ($this->authChecker->isGranted('ROLE_SUPER_ADMIN')) {
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
        return !$this->userLoader->isGuest();
    }

    private function canView()
    {
        $profile = $this->subject;
        $user = $this->userLoader->getUser();
        $author = $profile->getAuthor();

        return $profile->isPublic() or $user === $author or $user->isUserTeacher($author);
    }

    private function canEdit()
    {
        return $this->canCreate() && $this->userLoader->getUser() === $profile->getAuthor() && !$profile->isPublic();
    }

    private function canDelete()
    {
        return $this->canEdit();
    }

    private function canAppoint()
    {
        return $this->authChecker->isGranted('IS_ACCOUNT_PAID')
            && $this->canCreate() && $this->canView()
            && $this->userLoader->getUser()->getCurrentProfile() !== $this->subject;
    }

    private function canCopy()
    {
        return $this->canCreate() && $this->canView();
    }
}
