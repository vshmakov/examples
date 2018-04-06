<?php

namespace AppBundle;

class DateTime {
private $dateTime;

public function __construct($dateTime=null) {
$this->dateTime=$dateTime ?? new \DateTime();
}

public function __toString() {
return $this->format('d.m.Y H:i:s');
}

public function __call($m, $p=[]) {
return call_user_func_array([$this->dateTime, $m], $p);
}

}