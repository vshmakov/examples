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
     * @return mixed
     */
    private function getValue(Query $query)
    {
        $result = $query->setMaxResults(1)->getOneOrNullResult();

        if (!\is_array($result)) {
            return $result;
        }

        foreach ($result as $value) {
            return $value;
        }
    }

    /**
     * @deprecated
     */
    private function getEntityRepository(string $class): ServiceEntityRepository
    {
        return $this->getentityManager()->getRepository($class);
    }
}
