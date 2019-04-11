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
}
