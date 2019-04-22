<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class SessionMarker
{
    /** @var SessionInterface */
    private $session;

    /** @var Request|null */
    private $request;

    public function __construct(SessionInterface $session, RequestStack $requestStack)
    {
        $this->session = $session;
        $this->request = $requestStack->getMasterRequest();
    }

    public function getKey(): string
    {
        if (null === $this->request) {
            return '';
        }

        $value = $this->request ? $this->request->getClientIp() : '';
        $session = $this->session;
        $key = 'VISIT_KEY';

        if (!$session->has($key)) {
            $session->set($key, $value);
        }

        return $session->get($key);
    }
}
