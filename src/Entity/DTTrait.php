<?php

namespace App\Entity;

Use App\DT;

trait DTTrait {
public function __construct() {
$this->initAddTime();
}

private function initAddTime($var="addTime") {
$this->$var=new DT();
}

protected function dt($dt) {
return DT::createFromDT($dt);
}
}