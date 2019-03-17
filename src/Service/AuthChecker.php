<?php

namespace App\Service;

use App\Security\Authorization\AdvancedAuthorizationCheckerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

final class AuthChecker implements AdvancedAuthorizationCheckerInterface
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function isGranted($attributes, $subject = null): bool
    {
        try {
            return $this->authorizationChecker->isGranted($attributes, $subject);
        } catch (AuthenticationCredentialsNotFoundException $exception) {
            return false;
        }
    }

    public function denyAccessUnlessGranted($attributes, $subject = null): void
    {
        if ((!$this->isGranted($attributes, $subject))) {
            throw new AccessDeniedHttpException();
        }
    }
}
