<?php

namespace App\Security\Voter;

use App\Service\AuthChecker;
use App\Service\UserLoader;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TeacherVoter extends Voter
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
        return $this->supportsUser($attribute, $subject)
            && (null === $subject || $subject->isTeacher());
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->voteOnNamedCallback($attribute, $subject, $token);
    }

    private function canAppoint(): bool
    {
        $teacher = $this->subject;

        return $this->canDisappointTeachers()
            && !$this->userLoader->isCurrentUserGuest()
            && !$this->userLoader->getUser()->isStudentOf($teacher);
    }

    private function canDisappoint(): bool
    {
        return $this->userLoader->getUser()->isStudentOf($this->subject);
    }

    private function canDisappointTeachers(): bool
    {
        return $this->canShowTeachers();
    }

    private function canShowTeachers(): bool
    {
        $authChecker = $this->authChecker;

        return $authChecker->isGranted('ROLE_USER') && !$authChecker->isGranted('ROLE_CHILD')
            && !$this->userLoader->getUser()->isTeacher();
    }
}
