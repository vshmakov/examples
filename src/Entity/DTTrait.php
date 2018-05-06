<?php

namespace App\Entity;

Use App\DT;

trait DTTrait {
use BaseTrait;

public function __construct() {
$this->initAddTime();
}

private function initAddTime($var="addTime") {
$this->$var=new DT();
}

private function dts($s) {
return DT::createFromTimestamp($s);
}

private function dt($dt) {
return DT::createFromDT($dt);
}

}