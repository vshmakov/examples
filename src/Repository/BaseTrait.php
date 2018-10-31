<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;

trait BaseTrait
{
    use \App\BaseTrait;

    protected function createQuery(string $dql): Query
    {
        return $this->getEntityManager()->createQuery($dql);
    }

    protected function getValue(Query $query)
    {
        return self::getValueByQuery($query);
    }

    public static function getValueByQuery(Query $query)
    {
        $result = ($query->setMaxResults(1)->getOneOrNullResult());

        if (!\is_array($result)) {
            return $result;
        }

        foreach ($result as $value) {
            return $value;
        }
    }

    protected function getEntityRepository(string $class): ServiceEntityRepository
    {
        return $this->getentityManager()->getRepository($class);
    }
}
