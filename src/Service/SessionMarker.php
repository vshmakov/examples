<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionMarker {
private $s;

public function __construct(SessionInterface $s) {
$this->s=$s;
}

public function getKey() {
$s=$this->s;
$key="VISIT_KEY";
if (!$s->has($key)) $s->set($key, $this->getRand());
$sid=$s->get($key);
return $sid;
}

private function getRand() {
$rand=substr(base64_encode(random_bytes(32)), 0, 32);
return $rand;
}
}