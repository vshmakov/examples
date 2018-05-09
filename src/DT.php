<?php

namespace App;

class DT extends \DateTime {
public static function createFromFormat($f, $s, $o=null) {
$dt=DateTime::createFromFormat($f, $s);
return ($dt && $dt->getTimestamp() > 0) ? self::createFromDT($dt) : false;
}

public static function createFromDT($dt) {
return ($dt instanceof \DateTimeInterface) ? self::createFromTimestamp($dt->getTimestamp()) : null;
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

public function getRoundDays() {
return round($this->getTimestamp()/DAY);
}

public function isPast() {
return $this->getTimestamp() < time();
}

public function getRoundUpDays() {
$t=$this->getTimestamp();
$d=(((int) ($t/DAY)));
return ($t % DAY == 0 ? $d : $d+1);
}

public function minSecFormat() {
return $this->format("i:s");
}
}