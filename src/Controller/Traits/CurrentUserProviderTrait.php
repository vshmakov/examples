<?php

namespace App\Controller\Traits;

use App\Entity\User;
use App\Security\User\CurrentUserProviderInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use  Webmozart\Assert\Assert;

trait CurrentUserProviderTrait
{
    private function getCurrentUserOrGuest(): User
    {
        Assert::isInstanceOf($this, ContainerAwareInterface::class);

        return $this->container
            ->get(CurrentUserProviderInterface::class)
            ->getCurrentUserOrGuest();
    }
}
