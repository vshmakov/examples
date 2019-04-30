<?php

namespace App\EventSubscriber;

use App\Entity\User\Role;
use App\Attempt\EventSubscriber\RouteTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Webmozart\Assert\Assert;

final  class DifferentHomepageControllerSubscriber implements EventSubscriberInterface
{
    use RouteTrait;
    private const  ROUTE = 'homepage';

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function onKernelController(FilterControllerEvent $event): void
    {
        if (!$this->isRoute(self::ROUTE, $event)) {
            return;
        }

        $controller = $event->getController();
        Assert::isArray($controller);

        if ($this->authorizationChecker->isGranted(Role::STUDENT)) {
            $controller[1] = 'studentHomepage';
        }

        if ($this->authorizationChecker->isGranted(Role::TEACHER)) {
            $controller[1] = 'teacherHomepage';
        }

        $event->setController($controller);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
