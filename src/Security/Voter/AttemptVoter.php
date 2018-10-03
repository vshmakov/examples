<?php

namespace App\Security\Voter;

use App\Service\AuthChecker;
use App\Repository\ExampleRepository;
use App\Repository\AttemptRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Entity\Attempt;
use App\Service\UserLoader;
use App\Repository\SessionRepository;

class AttemptVoter extends Voter
{
    use BaseTrait;
    private $userLoader;
    private $sessionRepository;
    private $attemptRepository;
    private $exampleRepository;
    private $authChecker;

    public function __construct(UserLoader $userLoader, SessionRepository $sessionRepository, AttemptRepository $attemptRepository, ExampleRepository $exampleRepository, AuthChecker $authChecker)
    {
        $this->userLoader = $userLoader;
        $this->sessionRepository = $sessionRepository;
        $this->attemptRepository = $attemptRepository;
        $this->exampleRepository = $exampleRepository;
        $this->authChecker = $authChecker;
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
        $attempt = $this->subject;

        if (!$this->canView()) {
            return false;
        }

        $userLoader = $this->userLoader;
        $user = $userLoader->getUser();

        if (($userLoader->isGuest() && $attempt->getSession() !== $this->sessionRepository->findOneByCurrentUser())
            or (0 == $attempt->getRemainedExamplesCount())
            or (0 == $attempt->getRemainedTime())) {
            return false;
        }

        return true;
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
