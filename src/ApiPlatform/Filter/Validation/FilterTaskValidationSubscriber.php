<?php

namespace App\ApiPlatform\Filter\Validation;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\ApiPlatform\Filter\SupportsTaskFilteringInterface;
use App\Attempt\EventSubscriber\RouteTrait;
use App\Entity\Task;
use App\Security\Voter\TaskVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class FilterTaskValidationSubscriber implements EventSubscriberInterface, SupportsTaskFilteringInterface
{
    use  RouteTrait;

    public const  FIELD = 'task';
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(EntityManagerInterface $entityManager, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->entityManager = $entityManager;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function onKernelView(GetResponseForControllerResultEvent $event): void
    {
        if (!$this->isTaskFiltering($event)) {
            return;
        }

        $query = $event->getRequest()->query;

        $task = $this->entityManager
            ->getRepository(Task::class)
            ->find($query->get(self::FIELD));

        if (null === $task) {
            throw  new  NotFoundHttpException('Task not found');
        }

        if (!$this->authorizationChecker->isGranted(TaskVoter::SHOW, $task)) {
            throw new AccessDeniedHttpException('You can not show this task data');
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onKernelView', EventPriorities::PRE_SERIALIZE],
        ];
    }

    public function isTaskFiltering(KernelEvent $event): bool
    {
        return $this->inRoutes(FilterUserValidationSubscriber::SUPPORTED_ROUTES, $event) && $event->getRequest()->query->has(self::FIELD);
    }
}
