<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use App\Repository\ExampleRepository as ExR;
use App\Repository\AttemptRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Entity\Attempt;
use App\Service\UserLoader;
use App\Repository\SessionRepository;

class AttemptVoter extends Voter
{
    use BaseTrait;
    private $ul;
    private $sR;
    private $attR;
    private $exR;
    private $ch;

    public function __construct(UserLoader $ul, SessionRepository $sR, AttemptRepository $attR, ExR $exR, AuthorizationCheckerInterface $ch)
    {
        $this->ul = $ul;
        $this->sR = $sR;
        $this->attR = $attR;
        $this->exR = $exR;
        $this->ch = $ch;
    }

    protected function supports($attribute, $subject)
    {
        return $subject instanceof Attempt;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->checkRight($attribute, $subject->setER($this->attR), $token);
    }

    private function canSolve($att)
    {
        if (!$this->canView($att)) {
            return false;
        }
        $ul = $this->ul;
        $u = $ul->getUser();

        if (($ul->isGuest() && $att->getSession() !== $this->sR->findOneByCurrentUser())
or (0 == $att->getRemainedExamplesCount())
or (0 == $att->getRemainedTime())) {
            return false;
        }

        return true;
    }

    private function canAnswer($att)
    {
        $ex = $this->exR->findLastUnansweredByAttempt($att);

        return $this->canSolve($att) && $ex;
    }

    private function canView($att)
    {
        if ($this->ch->isGranted('ROLE_ADMIN')) {
            return true;
        }
        $ul = $this->ul;
        $u = $ul->getUser();

        if ($u !== $att->getSession()->getUser()) {
            return false;
        }

        return true;
    }
}
