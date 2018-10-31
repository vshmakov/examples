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
        return $this->checkRight($attribute, $subject, $token);
    }

    private function canAppoint()
    {
        $teacher = $this->subject;

        return $this->canDisappointTeachers()
            && !$this->userLoader->isGuest()
            && !$this->userLoader->getUser()->isUserTeacher($teacher);
    }

    private function canDisappoint()
    {
        return $this->userLoader->getUser()->isUserTeacher($this->subject);
    }

    private function canDisappointTeachers()
    {
        return $this->canShowTeachers();
    }

    private function canShowTeachers()
    {
        $authChecker = $this->authChecker;

        return $authChecker->isGranted('ROLE_USER') && !$authChecker->isGranted('ROLE_CHILD')
        && !$this->userLoader->getUser()->isTeacher();
    }
}
