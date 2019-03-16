<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;

/**
 * @deprecated
 */
trait BaseTrait
{
    /**
     * @deprecated
     */
    private function createQuery(string $dql): Query
    {
        return $this->getEntityManager()->createQuery($dql);
    }

    /**
     * @deprecated
     */
    private function getEntityRepository(string $class): ServiceEntityRepository
    {
        return $this->getentityManager()->getRepository($class);
    }
}
