<?php

Namespace AppBundle\Model;

use AppBundle\DateTime;

class Profiles extends Base {

public function __construct() {
$this->createTime=new \DateTime();
$this->isPublic=false;
}
   
public function getCreateTime() {
return new DateTime($this->createTime);
}

public function normalizePercents() {
$pKeies=['addPerc', 'subPerc', 'multPerc', 'divPerc'];
$p=[];
$all1=0;

foreach ($pKeies as $key) {
$all1+=$p[$key]=abs($this->$key);
}

if (!$all1) $all1=1;
$all2=0;
foreach ($p as $key=>$val) {
$all2+=$p[$key]=round($val/$all1*100);
}

foreach ($p as $key=>$val) {
$this->$key=$val;
}

foreach (array_reverse($p) as $key=>$val) {
if ($this->$key) return $this->$key+=100-$all2;
}

$this->$key+=100-$all2;
}

public function getData() {
return $this;
}

}