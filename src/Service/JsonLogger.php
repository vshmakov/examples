<?php

namespace App\Service;

use   Psr\Log\LoggerInterface as L;

class JsonLogger {
private $l;
private $level;

public function __construct(L $l)
{
$this->l=$l;
}

public function setLevel($lv) {
$this->level=$lv;
return $this;
}

public function __call($m, $p)
{
$isLog=$m == "log";
$i=!$isLog ? 0 : 1;
if ($isLog && $this->level) $p[0]=$this->level;
$p[$i]=json_encode($p[$i]);
$this->l->$m(...$p);
return $this;
}
}