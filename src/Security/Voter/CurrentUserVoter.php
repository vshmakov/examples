<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Security\User\CurrentUserProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class CurrentUserVoter extends BaseVoter
{
    public const  CREATE_PROFILES = 'CREATE_PROFILES';
    public const  SHOW_HOMEWORK = 'SHOW_HOMEWORK';

    /** @var User */
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

    protected static function getSupportedAttributes(): array
    {
        return [
            self::CREATE_PROFILES,
            self::SHOW_HOMEWORK,
        ];
    }

    protected function supports($attribute, $subject)
    {
        return null === $subject && $this->inSupportedAttributes($attribute);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        return parent::voteOnAttribute($attribute, $this->currentUserProvider->getCurrentUserOrGuest(), $token);
    }

    protected function canCreateProfiles(): bool
    {
        return $this->authorizationChecker->isGranted(User\Role::USER);
    }

    protected function canShowHomework(): bool
    {
        return $this->subject->hasTeacher();
    }
}
