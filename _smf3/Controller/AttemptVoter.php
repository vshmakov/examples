<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Entity\Attempt;
use App\Entity\User;
use App\Service\UserLoader;

class AttemptVoter extends Voter
{
private $ul;

public function __construct(UserLoader $ul) {
$this->ul=$ul;
}

    protected function supports($attribute, $subject)
    {
        return $subject instanceof Attempt;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        switch ($attribute) {
            case "EDIT":
return true;
                break;
            case 'VIEW':
return true;
                break;
        }

        return false;
    }

}
