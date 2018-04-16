<?php

namespace App\Security\Voter;

use App\Repository\AttemptRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Entity\Attempt;
use App\Entity\User;
use App\Service\UserLoader;
use App\Repository\SessionRepository;

class AttemptVoter extends Voter
{
private $ul;
private $att;
private $sR;
private $attR;

public function __construct(UserLoader $ul, SessionRepository $sR, AttemptRepository $attR) {
$this->ul=$ul;
$this->sR=$sR;
$this->attR=$attR;
}

    protected function supports($attribute, $subject)
    {
        return $subject instanceof Attempt;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
$this->att=$subject->setER($this->attR);

        switch ($attribute) {
case "SOLVE":
return $this->canSolve();
break;
case "ANSWER":
return $this->canAnswer();
break;
            case "EDIT":
return true;
                break;
            case 'VIEW':
return $this->canView();
                break;
        }

        return false;
    }

private function canSolve() {
if (!$this->canView()) return false;
$att=$this->att;
$ul=$this->ul;
$u=$ul->getUser();
$set=$att->getSettings();
dump($set);
if (($ul->isGuest() && $att->getSession() !== $this->sR->findOneByCurrentUser())
or ($att->getSolvedExamplesCount() >= $set->getExamplesCount()
or ($att->getAddTime()->getTimestamp() + $set->getDuration() < time())) ) return false;
return true;
}

private function canView() {
$att=$this->att;
$ul=$this->ul;
$u=$ul->getUser();
if ($u !== $att->getSession()->getUser()) return false;
return true;

}

private function canAnswer() {

}

}