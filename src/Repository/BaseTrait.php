<?php

namespace App\Repository;

trait BaseTrait
{
    use \App\BaseTrait;

    protected function createQuery($dql)
    {
        return $this->getEntityManager()->createQuery($dql);
    }

    protected function getValue($query)
    {
        return self::getValueByQuery($query);
    }

    public static function getValueByQuery($query)
    {
        $result = ($query->setMaxResults(1)->getOneOrNullResult());

        if (!is_array($result)) {
            return $result;
        }

        foreach ($result as $value) {
            return $value;
        }
    }

    protected function getEntityRepository($class)
    {
        return $this->getentityManager()->getRepository($class);
    }
}
