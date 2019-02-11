<?php

namespace App\Security\Voter;

use App\Entity\Attempt;
use App\Repository\AttemptRepository;
use App\Repository\ExampleRepository;
use App\Security\User\CurrentUserProviderInterface;
use App\Security\User\CurrentUserSessionProviderInterface;
use App\Service\AuthChecker;
use App\Service\UserLoader;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Webmozart\Assert\Assert;

final class AttemptVoter extends Voter
{
    public const VIEW = 'VIEW';
    use BaseTrait;

    private $userLoader;
    private $attemptRepository;
    private $exampleRepository;
    private $authChecker;

    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;

    /** @var CurrentUserSessionProviderInterface */
    private $currentUserSessionProvider;

    public function __construct(
        UserLoader $userLoader,
        AttemptRepository $attemptRepository,
        ExampleRepository $exampleRepository,
        AuthChecker $authChecker,
        CurrentUserProviderInterface $currentUserProvider,
        CurrentUserSessionProviderInterface $currentUserSessionProvider
    ) {
        $this->userLoader = $userLoader;
        $this->attemptRepository = $attemptRepository;
        $this->exampleRepository = $exampleRepository;
        $this->authChecker = $authChecker;
        $this->currentUserProvider = $currentUserProvider;
        $this->currentUserSessionProvider = $currentUserSessionProvider;
    }

    protected function supports($attribute, $subject)
    {
        return $subject instanceof Attempt;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->checkRight($attribute, $subject->setEntityRepository($this->attemptRepository), $token);
    }

    private function canSolve()
    {
        /** @var Attempt $attempt */
        $attempt = $this->subject;

        return $this->canView()
            && $this->isCurrentSessionAttempt($attempt)
            && $attempt->getRemainedExamplesCount() > 0
            && $attempt->getRemainedTime()->getTimestamp() > 0;
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
        if ($this->authChecker->isGranted('ROLE_ADMIN')) {
            return true;
        }

        $attempt = $this->subject;
        $userLoader = $this->userLoader;
        $user = $userLoader->getUser();
        $author = $attempt->getSession()->getUser();

        return $user === $author or $author->isUserTeacher($user);
    }
}
