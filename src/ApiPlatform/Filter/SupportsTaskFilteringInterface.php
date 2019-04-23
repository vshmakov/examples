<?php

namespace App\ApiPlatform\Filter;

use Symfony\Component\HttpKernel\Event\KernelEvent;

interface SupportsTaskFilteringInterface
{
    public function isTaskFiltering(KernelEvent $event): bool;
}
