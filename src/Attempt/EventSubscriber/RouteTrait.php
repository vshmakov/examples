<?php

namespace App\Attempt\EventSubscriber;

use App\ApiPlatform\Attribute;
use Symfony\Component\HttpKernel\Event\KernelEvent;

trait RouteTrait
{
    private function isRoute(string $route, KernelEvent $event): bool
    {
        return $route === $event->getRequest()->attributes->get(Attribute::ROUTE);
    }

    private function inRoutes(array $routes, KernelEvent $event): bool
    {
        return array_reduce($routes, function (bool $inRoutes, string $route) use ($event): bool {
            if (true === $inRoutes) {
                return true;
            }

            return $this->isRoute($route, $event);
        }, false);
    }
}
