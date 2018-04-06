<?php

namespace AppBundle\Model;

class Examples extends Base {

public function __construct() {
$this->addTime=new \DateTime();
}

public function getData() {
return ['string'=>$this->getExampleString(), 'number'=>$this->getNumber()];
}

public function getExampleString() {
return $this->first.' '.[1=>'+', '-', '*', ':'][$this->sign].' '.$this->second;
}

public function setAnswer($answer) {
$this->answer=$answer;
$rightAnswer=$this->solve($this->first, $this->second, $this->sign);
return $this->isRight=($answer == $rightAnswer);
}

protected function solve($a, $b, $sign) {
switch ((int) $sign) {
case 1:
return $a+$b;
break;
case 2:
return $a-$b;
break;
case 3:
return $a*$b;
break;
case 4:
return $a/$b;
break;
}

}

public function getAddTime() {
return new \AppBundle\DateTime($this->addTime);
}

public function getNumber() {
return createQuery(
"select count(e.id) from %s e 
where e.id <= :id and e.try = :try", ['e']
)->setParameters(['id'=>$this->id, 'try'=>$this->try])->getOneOrNullResult()[1];
}

public function __toString() {
return $this->getExampleString();
}

public function getErrorNum() {
return ($this->isRight() === false) ? createQuery(
'select count(e) from %s e
where e.try = :try and e.id < :id and e.isRight = false', ['e']
)->setParameters(['id'=>$this->id, 'try'=>$this->try])->getOneOrNullResult()[1]+1
: null;
}

}