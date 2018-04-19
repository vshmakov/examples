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

public function __construct(ExMng $m) {
$this->exMng=$m;
}

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Example::class);
    }

public function findLastByAttempt($att) {

}

public function getNumber($ex) {

}

public function findLastByAttemptOrGetNew($att) {
return $this->findLastByAttempt($att) ?? $this->getNew($att);
}

public function getNew($att) {
$ex=new Example();
$ex->setAttempt($att);
$d=$this->ExMng->getRandEx($att->getSettings()->getData());
$ex->setFirst($d->first)->setSecond($d->second)->setSign($d->sign);
$em=$this->em();
$em->persist($ex);
$em->flush();
return $ex;
}

}