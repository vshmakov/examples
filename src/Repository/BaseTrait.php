<?php

namespace App\Repository;

use App\DT;
use App\Entity\Example;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

trait BaseTrait {
private function em() {
return $this->getEntityManager();
}

private function dt($dt) {
return DT::createFromDT($dt);
}

private function q($dql) {
return $this->em()->createQuery($dql);
}

private function v($q) {
return ($q->setMaxResults(1)->getOneOrNullResult())[1]; //getSingleResult()[1];
}

private function qb() {
return $this->createQueryBuilder();
}
}