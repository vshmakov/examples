<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class SessionMarker
{
    private $session;
    private $request;

    public function __construct(SessionInterface $session, RequestStack $requestStack)
    {
        $this->session = $session;
        $this->request = $requestStack->getMasterRequest();
    }

    public function getKey()
    {
        $value = $this->request ? $this->request->getClientIp() : '';
        $session = $this->session;
        $key = 'VISIT_KEY';

        if (!$session->has($key)) {
            $session->set($key, $value);
        }

        $sid = $session->get($key);

        return $sid;
    }
}
