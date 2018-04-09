<?php

namespace App;

class DT extends \DateTime {
public static function createFromFormat($f, $s) {
$dt=DateTime::createFromFormat($f, $s);
return ($dt && $dt->getTimestamp() > 0) ? self::createFromDT($dt) : false;
}

public static function createFromDT($dt) {
return self::createFromTimestamp($dt->getTimestamp());
}

public static function createFromTimestamp($time) {
$dt= new self();
return $dt->setTimestamp($time);
}

public function dbFormat() {
return $this->format('Y-m-d H:i:s');
}

public function stFormat() {
return $this->format('d.m.Y H:i:s');
}

public function timeFormat() {
return $this->format('H:i:s');
}

public function dateFormat() {
return $this->format('d.m.y');
}

public function setDayS() {
return $this->setTime(0, 0);
}

public function setDayF() {
return $this->setTime(23, 59, 59);
}

public function __toString() {
return $this->stFormat();
}

}