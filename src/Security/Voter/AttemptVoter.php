<?php

namespace App\Security\Voter;

use App\Entity\Attempt;
use App\Entity\User\Role;
use App\Repository\AttemptRepository;
use App\Repository\ExampleRepository;
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

    private $attemptRepository;
    private $exampleRepository;
    private $authorizationChecker;

    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;

    /** @var CurrentUserSessionProviderInterface */
    private $currentUserSessionProvider;

    public function __construct(
        AttemptRepository $attemptRepository,
        ExampleRepository $exampleRepository,
        AuthorizationCheckerInterface $authorizationChecker,
        CurrentUserProviderInterface $currentUserProvider,
        CurrentUserSessionProviderInterface $currentUserSessionProvider
    ) {
        $this->attemptRepository = $attemptRepository;
        $this->exampleRepository = $exampleRepository;
        $this->authorizationChecker = $authorizationChecker;
        $this->currentUserProvider = $currentUserProvider;
        $this->currentUserSessionProvider = $currentUserSessionProvider;
    }

    protected function supports($attribute, $subject)
    {
        return $subject instanceof Attempt;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->checkRight($attribute, $subject, $token);
    }

    private function canSolve()
    {
        /** @var Attempt $attempt */
        $attempt = $this->subject;

        return $this->canView()
            && $this->isCurrentSessionAttempt($attempt);
    }

    private function isCurrentSessionAttempt(Attempt $attempt): bool
    {
        if (!$this->currentUserProvider->isCurrentUserGuest()) {
            return true;
        }

        $session = $attempt->getSession();
        Assert::notNull($session);

        return $this->currentUserSessionProvider->isCurrentUserSession($session);
    }

    private function canAnswer()
    {
        $attempt = $this->subject;
        $example = $this->exampleRepository->findLastUnansweredByAttempt($attempt);

        return $this->canSolve() && $example;
    }

    private function canView()
    {
        if ($this->authorizationChecker->isGranted(Role::ADMIN)) {
            return true;
        }

        $attempt = $this->subject;
        $user = $this->currentUserProvider->getCurrentUserOrGuest();
        $author = $attempt->getSession()->getUser();

        return $author->isEqualTo($user) or $author->isUserTeacher($user);
    }
}
