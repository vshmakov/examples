<?php

namespace App\Security\Voter;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Service\AuthChecker;
use App\Service\UserLoader;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class HomeworkVoter extends Voter
{
    use BaseTrait;
    private $userLoader;
    private $authChecker;
    private $taskRepository;

    public function __construct(UserLoader $userLoader, AuthChecker $authChecker, TaskRepository $taskRepository)
    {
        $this->userLoader = $userLoader;
        $this->authChecker = $authChecker;
        $this->taskRepository = $taskRepository;
    }

    protected function supports($attribute, $subject)
    {
        return $subject instanceof Task && $this->hasHandler($attribute);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->voteOnNamedCallback($attribute, $subject, $token);
    }

    private function canSolve(): bool
    {
        $task = $this->subject;
        $currentUser = $this->userLoader->getUser();

        return $this->canShowHomeworks()
            && $task->getContractors()->contains($currentUser)
            && !$this->taskRepository->isDoneByUser($task, $currentUser)
            && $task->getAddTime()->getTimestamp() < time()
            && $task->getLimitTime()->getTimestamp() > time();
    }

    private function canShowExamples(): bool
    {
        return $this->subject->getContractors()
            ->contains($this->userLoader->getUser());
    }

    private function canShowAttempts(): bool
    {
        return $this->canShowExamples();
    }
}
