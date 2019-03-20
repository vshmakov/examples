<?php

namespace App\Security\Voter;

use App\Entity\Attempt;
use App\Entity\User\Role;
use App\Security\User\CurrentUserProviderInterface;
use App\Security\User\CurrentUserSessionProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Webmozart\Assert\Assert;

final class AttemptVoter extends Voter
{
    use BaseTrait;

    public const SOLVE = 'SOLVE';
    public const VIEW = 'VIEW';

    /** @var Attempt */
    private $subject;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;

    /** @var CurrentUserSessionProviderInterface */
    private $currentUserSessionProvider;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        CurrentUserProviderInterface $currentUserProvider,
        CurrentUserSessionProviderInterface $currentUserSessionProvider
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->currentUserProvider = $currentUserProvider;
        $this->currentUserSessionProvider = $currentUserSessionProvider;
    }

    private function getSupportedAttributes(): array
    {
        return [
            self::VIEW,
            self::SOLVE,
        ];
    }

    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof Attempt;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        Assert::oneOf($attribute, self::getSupportedAttributes());

        return $this->voteOnNamedCallback($attribute, $subject, $token);
    }

    private function canView(): bool
    {
        if ($this->authorizationChecker->isGranted(Role::ADMIN)) {
            return true;
        }

        $currentUser = $this->currentUserProvider->getCurrentUserOrGuest();
        $author = $this->subject->getSession()->getUser();

        return $currentUser->isEqualTo($author) or $currentUser->isTeacherOf($author);
    }

    private function canSolve(): bool
    {
        return $this->canView()
            && $this->createdByCurrentSessionUser($this->subject);
    }

    private function createdByCurrentSessionUser(Attempt $attempt): bool
    {
        if (!$this->currentUserProvider->isCurrentUserGuest()) {
            return true;
        }

        $session = $attempt->getSession();
        Assert::notNull($session);

        return $this->currentUserSessionProvider->isCurrentUserSession($session);
    }
}
