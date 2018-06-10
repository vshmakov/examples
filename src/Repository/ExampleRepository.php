<?php

namespace App\Repository;

use App\Entity\Example;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Service\ExampleManager as ExMNG;
use App\Service\UserLoader;

class ExampleRepository extends ServiceEntityRepository
{
use BaseTrait;
private $exMng;
private $ul;

   public function __construct(RegistryInterface $registry, ExMng $m, UserLoader $ul)
    {
        parent::__construct($registry, Example::class);
$this->exMng=$m;
$this->ul=$ul;
    }

public function findLastUnansweredByAttempt($att) {
return $this->v($this->q("select e from App:Example e
where e.attempt = :a and e.answer is null
order by e.addTime desc")
->setParameter("a", $att));
}

public function findLastByAttempt($att) {
return $this->v($this->q("select e from App:Example e
where e.attempt = :a 
order by e.addTime desc")
->setParameter("a", $att));
}

public function getErrorNum($ex) {
if ($ex->isRight() !== false) return;
return $this->v($this->q("select count(e) from App:Example e
where e.attempt = :a and e.isRight = false and e.addTime <= :dt")
->setParameters(["a"=>$ex->getAttempt(), "dt"=>$ex->getAddTime()]));
}

public function getNumber($ex) {
$wh=$ex->getAttempt()->getSettings()->isDemanding() ? " and e.isRight != false" : "";
return $this->v($this->q("select count(e) from App:Example e
where e.attempt = :a and e.addTime < :dt $wh")
->setParameters(["a"=>$ex->getAttempt(), "dt"=>$ex->getAddTime()]))+1;
}

public function findLastUnansweredByAttemptOrGetNew($att) {
return $this->findLastUnansweredByAttempt($att) ?? $this->getNew($att);
}

public function getNew($att) {
$ex=(new Example)->setAttempt($att);
if ($att->getSettings()->isDemanding() && ($l=$this->findLastByAttempt($att)) && !$l->isRight()) {
$ex->setFirst($l->getFirst())->setSecond($l->getSecond())->setSign($l->getSign());
} else {
($set=$att->getSettings()->getData());
$m=$this->exMng;
$s=$m->getRandSign($set);
$exs=$this->q("select e from App:Example e
join e.attempt a
join a.session s
join s.user u
where u = :u and a.addTime > :dt")
->setParameters(["u"=>$this->ul->getUser(), "dt"=>(new \DateTime)->sub(new \DateInterval("P7D"))])
->getResult();
$d=$m->getRandEx($s, $set, $exs);
$ex->setFirst($d->first)->setSecond($d->second)->setSign($d->sign);
}
$em=$this->em();
$em->persist($ex);
$em->flush();
return $ex;
}

public function getSolvingTime($ex) {
$s=null;

if ($ex->getAnswerTime()) {
$p=$this->v($this->q("select e from App:Example e
where e.attempt = :att and e.addTime < :dt
order by e.addTime desc")
->setParameters(["dt"=>$ex->getAddTime(), "att"=>$ex->getAttempt()]));

$f=$p ? $p->getAnswerTime() : $ex->getAttempt()->getAddTime();
$s=$ex->getAnswerTime()->getTimestamp() - $f->getTimestamp();
}

return $this->dts($s);
}
}