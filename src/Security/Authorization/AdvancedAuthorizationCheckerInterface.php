<?php

declare(strict_types=1);

namespace App\Security\Authorization;

use  Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

interface AdvancedAuthorizationCheckerInterface extends AuthorizationCheckerInterface
{
    /**
     * @throws AccessDeniedHttpException
     */
    public function denyAccessUnlessGranted($attributes, $subject = null): void;
}
