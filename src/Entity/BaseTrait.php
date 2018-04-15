<?php

namespace App\Entity;

Use App\DT;

trait BaseTrait {
private $er;

public function setER($er) {
$this->er=$er;
return $this;
}

public function __call($v, $p=[]) {
$m="get".ucfirst($v);
return $this->er->$m($this);
}
}