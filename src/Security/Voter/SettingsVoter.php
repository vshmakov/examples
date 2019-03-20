<?php

namespace App\Security\Voter;

use App\Entity\Settings;
use App\Service\AuthChecker;
use App\Service\UserLoader;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SettingsVoter extends Voter
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
        return $subject instanceof Settings && $this->hasHandler($attribute);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (\is_array($subject)) {
            return $this->voteOnArr($attribute, $subject, $token);
        }

        return $this->voteOnNamedCallback($attribute, $subject, $token);
    }

    private function canShow(): bool
    {
        return true;
    }
}
