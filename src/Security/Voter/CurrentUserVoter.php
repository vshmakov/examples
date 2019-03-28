<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Security\User\CurrentUserProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class CurrentUserVoter extends Voter
{
    use BaseTrait;

    public const  CREATE_PROFILES = 'create_profiles';

    /** @var User */
    private $subject;

    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(CurrentUserProviderInterface $currentUserProvider, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->currentUserProvider = $currentUserProvider;
        $this->authorizationChecker = $authorizationChecker;
    }

    protected function supports($attribute, $subject)
    {
        return null === $subject && $this->supportsAttribute($attribute);
    }

    private function supportsAttribute(string $attribute): bool
    {
        return \in_array($attribute, [self::CREATE_PROFILES], true);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->voteOnNamedCallback($attribute, $subject, $token);
    }

    private function canCreateProfiles(): bool
    {
        return $this->authorizationChecker->isGranted(User\Role::USER);
    }
}
