<?php

namespace App\Security\Voter;

use App\Repository\TaskRepository;
use App\Service\AuthChecker;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Service\UserLoader;
use App\Entity\Task;

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
        return (($subject instanceof Task) or null === $subject) && $this->hasHandler($attribute);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->checkRight($attribute, $subject, $token);
    }

    private function canShowHomework() : bool
    {
        $currentUser = $this->userLoader->getUser();

        return $this->authChecker->isGranted('ROLE_USER')
            && (!$currentUser->isTeacher() or $currentUser->getHomework()->count());
    }


    private function canSolve() : bool
    {
        $task = $this->subject;
        $currentUser = $this->userLoader->getUser();

        return $this->canShowHomework()
            && $task->getContractors()->contains($currentUser)
            && !$this->taskRepository->isDoneByUser($task, $currentUser)
            && $task->getLimitTime()->getTimestamp() > time();
    }

}