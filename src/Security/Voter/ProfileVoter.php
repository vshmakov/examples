<?php

namespace App\Security\Voter;

use App\Entity\Profile;
use App\Service\AuthChecker;
use App\Service\UserLoader;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class ProfileVoter extends Voter
{
    use BaseTrait;

    public const  VIEW = 'view';
    public const  EDIT = 'edit';
    public const  DELETE = 'delete';
    public const  APPOINT = 'appoint';

    private $userLoader;
    private $authChecker;

    public function __construct(UserLoader $userLoader, AuthChecker $authChecker)
    {
        $this->userLoader = $userLoader;
        $this->authChecker = $authChecker;
    }

    protected function supports($attribute, $subject)
    {
        return !\is_array($subject) ? ($subject instanceof Profile or null === $subject && $this->hasHandler($attribute)) : $this->supportsArr($attribute, $subject);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (\is_array($subject)) {
            return $this->voteOnArr($attribute, $subject, $token);
        }

        if ($this->authChecker->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        return $this->voteOnNamedCallback($attribute, $subject, $token);
    }

    private function canCreateProfile(): bool
    {
        return $this->canCreate();
    }

    private function canCreate(): bool
    {
        return !$this->userLoader->isCurrentUserGuest();
    }

    private function canView(): bool
    {
        $profile = $this->subject;
        $user = $this->userLoader->getUser();
        $author = $profile->getAuthor();

        return $profile->isPublic() or $user === $author or $user->isStudentOf($author);
    }

    private function canEdit(): bool
    {
        $profile = $this->subject;

        return $this->canCreate() && $this->userLoader->getUser() === $profile->getAuthor() && !$profile->isPublic();
    }

    private function canDelete(): bool
    {
        return $this->canEdit();
    }

    private function canAppoint(): bool
    {
        return $this->canUse()
            && $this->userLoader->getUser()->getCurrentProfile() !== $this->subject;
    }

    private function canUse(): bool
    {
        return $this->authChecker->isGranted('IS_ACCOUNT_PAID')
            && $this->canCreate() && $this->canView();
    }

    private function canCopy(): bool
    {
        return $this->canCreate() && $this->canView();
    }
}
