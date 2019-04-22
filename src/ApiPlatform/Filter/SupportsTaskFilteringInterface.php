<?php

declare(strict_types=1);

namespace App\ApiPlatform\Filter;

use Symfony\Component\HttpKernel\Event\KernelEvent;

interface SupportsTaskFilteringInterface
{
    public function isTaskFiltering(KernelEvent $event): bool;
}
