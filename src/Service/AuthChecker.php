<?php

namespace App\Service;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface as CH;

class AuthChecker
{
private $ch;

public function __construct(CH $ch)
{
$this->ch=$ch;
}

public function isGranted(...$p) {
try {
return $this->ch->isGranted(...$p);
} catch(\Exception $ex) {
return false;
}
}
}