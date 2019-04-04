<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\User\Role;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class UserVoter extends BaseVoter
{
    public const  APPOINT_TEACHER = 'APPOINT_TEACHER';

    /** @var User */
    protected $subject;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof User;
    }

    protected static function getSupportedAttributes(): array
    {
        return [
            self::APPOINT_TEACHER,
        ];
    }

    protected function canAppointTeacher(): bool
    {
        return$this->authorizationChecker->isGranted(Role::USER) && $this->subject->isTeacher();
    }

    protected function canLoginAs(): bool
    {
        return $this->authorizationChecker->isGranted(Role::SUPER_ADMIN);
    }
}
