<?php

namespace App\Security\Voter;

use App\Repository\ExampleRepository as ExR;
use App\Repository\AttemptRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Entity\Attempt;
use App\Entity\User;
use App\Service\UserLoader;
use App\Repository\SessionRepository;

class AttemptVoter extends Voter
{
use BaseTrait;
private $ul;
private $att;
private $sR;
private $attR;
private $exR;

public function __construct(UserLoader $ul, SessionRepository $sR, AttemptRepository $attR, ExR $exR) {
$this->ul=$ul;
$this->sR=$sR;
$this->attR=$attR;
$this->exR=$exR;
}

    protected function supports($attribute, $subject)
    {
        return $subject instanceof Attempt;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
$this->att=$subject->setER($this->attR);
return $this->checkRight($attribute, $subject, $token);
    }

private function canSolve() {
if (!$this->canView()) return false;
$att=$this->att;
$ul=$this->ul;
$u=$ul->getUser();
$set=$att->getSettings();
if (($ul->isGuest() && $att->getSession() !== $this->sR->findOneByCurrentUser())
or ($att->getSolvedExamplesCount() >= $set->getExamplesCount()
or ($att->getAddTime()->getTimestamp() + $set->getDuration() < time())) ) return false;
return true;
}

private function canAnswer() {
$ex=$this->exR->findLastByAttempt($this->att);
return $this->canSolve() && $ex && $ex->getAnswer() === null;
}

private function canView() {
$att=$this->att;
$ul=$this->ul;
$u=$ul->getUser();
if ($u !== $att->getSession()->getUser()) return false;
return true;
}

}