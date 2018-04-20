<?php

namespace App\Repository;

use App\Entity\Example;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Service\ExampleManager as ExMNG;

class ExampleRepository extends ServiceEntityRepository
{
use BaseTrait;
private $exMng;

   public function __construct(RegistryInterface $registry, ExMng $m)
    {
        parent::__construct($registry, Example::class);
$this->exMng=$m;
    }

public function findLastUnansweredByAttempt($att) {
return $this->v($this->q("select e from App:Example e
where e.attempt = :a and e.answer is null
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
return $this->v($this->q("select count(e) from App:Example e
where e.attempt = :a and e.addTime <= :dt")
->setParameters(["a"=>$ex->getAttempt(), "dt"=>$ex->getAddTime()]));
}

public function findLastUnansweredByAttemptOrGetNew($att) {
return $this->findLastUnansweredByAttempt($att) ?? $this->getNew($att);
}

public function getNew($att) {
$ex=new Example();
$ex->setAttempt($att);
($set=$att->getSettings()->getData());
$d=$this->exMng->getRandEx($set);
$ex->setFirst($d->first)->setSecond($d->second)->setSign($d->sign);
$em=$this->em();
$em->persist($ex);
$em->flush();
return $ex;
}

}