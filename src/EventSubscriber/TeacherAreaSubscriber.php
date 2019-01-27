<?php

namespace App\EventSubscriber;

use App\Controller\StudentController;
use App\Controller\TaskController;
use App\Exception\RequiresTeacherAccessException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class TeacherAreaSubscriber implements EventSubscriberInterface
{
    private const TEACHER_AREA = [
        StudentController::class,
        TaskController::class,
    ];

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function onKernelController(FilterControllerEvent $event): void
    {
        $controller = $event->getController();

        if (!\is_array($controller)) {
            return;
        }

        $controllerClass = \get_class($controller[0]);

        if (\in_array($controllerClass, self::TEACHER_AREA, true) && !$this->authorizationChecker->isGranted('ROLE_TEACHER')) {
            throw new RequiresTeacherAccessException();
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
