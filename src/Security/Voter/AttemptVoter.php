<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Attempt;
use App\Entity\User\Role;
use App\Security\User\CurrentUserProviderInterface;
use App\Security\User\CurrentUserSessionProviderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Webmozart\Assert\Assert;

final class AttemptVoter extends BaseVoter
{
    public const VIEW = 'VIEW';
    public const SOLVE = 'SOLVE';

    /** @var Attempt */
    protected $subject;

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

    protected static function getSupportedAttributes(): array
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

    protected function canView(): bool
    {
        if ($this->authorizationChecker->isGranted(Role::ADMIN)) {
            return true;
        }

        $currentUser = $this->currentUserProvider->getCurrentUserOrGuest();
        $author = $this->subject->getSession()->getUser();

        return $currentUser->isEqualTo($author) or $currentUser->isTeacherOf($author);
    }

    protected function canSolve(): bool
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
