<?php

namespace App\Repository;

use App\DT;
use App\Entity\Example;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

trait BaseTrait {
use \App\BaseTrait;

public function em() {
return $this->getEntityManager();
}

public function q($dql) {
return $this->em()->createQuery($dql);
}

public function v($q) {
$r=($q->setMaxResults(1)->getOneOrNullResult());
if (!is_array($r)) return $r;
foreach ($r as $v) {
return $v;
}
}

private function qb() {
return $this->createQueryBuilder();
}

private function er($cl) {
return $this->em()->getRepository($cl);
}
}