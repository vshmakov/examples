<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Task;
use App\Security\User\CurrentUserProviderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class TaskVoter extends BaseVoter
{
    public const  SHOW = 'SHOW';
    public const  EDIT = 'EDIT';
    public const  DELETE = 'DELETE';
    public const  SOLVE = 'SOLVE';

    /** @var Task */
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

    protected function supports($attribute, $subject)
    {
        return $subject instanceof Task;
    }

    protected static function getSupportedAttributes(): array
    {
        return [
            self::SHOW,
            self::EDIT,
            self::SOLVE,
        ];
    }

    protected function canShow(): bool
    {
        return $this->subject->isCreatedBy($this->currentUserProvider->getUser());
    }

    protected function canEdit(): bool
    {
        return $this->canShow();
    }

    protected function canDelete(): bool
    {
        return $this->canShow();
    }

    protected function canSolve(): bool
    {
        return $this->subject->getContractors()->contains($this->currentUserProvider->getCurrentUserOrGuest());
    }
}
