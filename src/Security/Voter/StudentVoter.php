<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Security\Voter\Traits\BaseTrait;
use App\Service\AuthChecker;
use App\Service\UserLoader;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class StudentVoter extends Voter
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
        return $this->supportsUser($attribute, $subject);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->voteOnNamedCallback($attribute, $subject, $token);
    }

    private function canShowAttempts()
    {
        return $this->subject->isUserTeacher($this->userLoader->getUser());
    }

    private function canShowExamples()
    {
        return $this->canShowAttempts();
    }

    private function canShowStudents()
    {
        $user = $this->userLoader->getUser();

        return $user->isTeacher() or $user->hasStudents();
    }
}
