<?php

namespace AppBundle\Model;

use AppBundle\Entity\Examples;
use AppBundle\Entity\Profiles;
use AppBundle\Entity\Sessions;
use AppBundle\DateTime as DT;

class Tries extends Base {

public function __construct() {
$this->startTime=new \DateTime();
}

public function getStartTime() {
return new DT($this->startTime);
}

public function getFinishTime() {
return ($ex=er('e')->getLastExampleByTryOrNull($this)) ? $ex->getAddTime() : $this->getStartTime();
}

public function getExamplesCount() {
return ($s=$this->getSettings()) ? $s->getExamplesCount() : '0';
}

public function getSolvedExamplesCount() {
return createQuery(
'select count(e.id) from %1$s e
where e.try = :try and e.answer is not null', ['e']
)->setParameter('try', $this)->getSingleResult()[1];
}

public function getErrorsCount() {
return createQuery(
"select count(e.id) from %s e
where e.isRight = false  and e.try = :try", ['e']
)->setParameter('try', $this)->getSingleResult()[1];
}

public function getRating() {
return 5;
}

public function getLimitTime() {
$seconds=$this->getStartTime()->getTimeStamp()+$this->getSettings()->getTryDuration();
$dt=new \DateTime();
return new DT($dt->setTimestamp($seconds));
}

public function getCurrentData() {
$e=er('e')->getLastOrNewExampleByTry($this);
return [
'example'=>$e->getData(),
'errorsCount'=>$this->getErrorsCount(),
'remainedExamplesCount'=>$this->getExamplesCount()-$e->getNumber(),
'limitTime'=>$this->getLimitTime()->getTimeStamp(),
];//
}

public function isActual() {
return er('t')->isActualTry($this);}

}