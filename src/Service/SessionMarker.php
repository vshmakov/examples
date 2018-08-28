<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class SessionMarker
{
    private $s;
    private $req;

    public function __construct(SessionInterface $s, RequestStack $rs)
    {
        $this->s = $s;
        $this->req = $rs->getMasterRequest();
    }

    public function getKey()
    {
        $val = $this->req ? $this->req->getClientIp() : $this->getRand();

        $s = $this->s;
        $key = 'VISIT_KEY';

        if (!$s->has($key)) {
            $s->set($key, $val);
        }
        $sid = $s->get($key);

        return $sid;
    }

    private function getRand()
    {
        $rand = substr(base64_encode(random_bytes(32)), 0, 32);

        return $rand;
    }
}
