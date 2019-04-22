<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\User\Role;
use App\Security\User\CurrentUserProviderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class UserVoter extends BaseVoter
{
    public const  APPOINT_TEACHER = 'APPOINT_TEACHER';
    public const  SHOW_SOLVING_RESULTS = 'SHOW_SOLVING_RESULTS';

    /** @var User */
    protected $subject;

    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(CurrentUserProviderInterface $currentUserProvider, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->currentUserProvider = $currentUserProvider;
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
            self::SHOW_SOLVING_RESULTS,
        ];
    }

    protected function canAppointTeacher(): bool
    {
        return $this->authorizationChecker->isGranted(Role::USER) && $this->subject->isTeacher();
    }

    protected function canLoginAs(): bool
    {
        return $this->authorizationChecker->isGranted(Role::SUPER_ADMIN);
    }

    protected function canShowSolvingResults(): bool
    {
        $currentUser = $this->currentUserProvider->getCurrentUserOrGuest();

        return $this->subject->isEqualTo($currentUser) or $this->subject->isStudentOf($currentUser);
    }
}
