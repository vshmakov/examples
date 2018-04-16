<?php

namespace App\Repository;

use App\DT;
use App\Entity\Example;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

trait BaseTrait {
use \App\BaseTrait;

private function em() {
return $this->getEntityManager();
}

private function q($dql) {
return $this->em()->createQuery($dql);
}

private function v($q) {
$r=($q->setMaxResults(1)->getOneOrNullResult());
return (is_array($r)) ? $r[1] : $r;
}

private function qb() {
return $this->createQueryBuilder();
}
}