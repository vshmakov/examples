<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Profile;
use App\Security\User\CurrentUserProviderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class ProfileVoter extends BaseVoter
{
    public const  VIEW = 'VIEW';
    public const  EDIT = 'EDIT';
    public const  DELETE = 'DELETE';
    public const  APPOINT = 'APPOINT';
    public const  COPY = 'COPY';

    /** @var Profile */
    protected $subject;

    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(CurrentUserProviderInterface $currentUserProvider, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->currentUserProvider = $currentUserProvider;
        $this->authorizationChecker = $authorizationChecker;
    }

    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof Profile;
    }

    protected static function getSupportedAttributes(): array
    {
        return [
            self::VIEW,
            self::EDIT,
            self::DELETE,
            self::APPOINT,
            self::COPY,
        ];
    }

    protected function canView(): bool
    {
        $author = $this->subject->getAuthor();

        return $this->subject->isPublic()
            or $this->currentUserProvider->getCurrentUserOrGuest()->isEqualTo($author);
    }

    protected function canEdit(): bool
    {
        $profile = $this->subject;

        return $this->authorizationChecker->isGranted(CurrentUserVoter::CREATE_PROFILES)
            && $this->currentUserProvider->getCurrentUserOrGuest()->isEqualTo($profile->getAuthor())
            && !$profile->isPublic();
    }

    protected function canDelete(): bool
    {
        return $this->canEdit();
    }

    protected function canAppoint(): bool
    {
        return $this->authorizationChecker->isGranted(CurrentUserVoter::CREATE_PROFILES) && $this->canView();
    }

    protected function canCopy(): bool
    {
        return $this->authorizationChecker->isGranted(CurrentUserVoter::CREATE_PROFILES)
            && $this->canView();
    }
}
