<?php

namespace App\Repository;

trait BaseTrait
{
    use \App\BaseTrait;

    public function em()
    {
        return $this->getEntityManager();
    }

    public function q($dql)
    {
        return $this->createQuery($dql);
    }

    public function createQuery($dql)
    {
        return $this->em()->createQuery($dql);
    }

    public function v($query)
    {
        return $this->getValue($query);
    }

    public function getValue($query)
    {
        $result = ($query->setMaxResults(1)->getOneOrNullResult());

        if (!is_array($result)) {
            return $result;
        }

        foreach ($result as $value) {
            return $value;
        }
    }

    private function getEntityRepository($class)
    {
        return $this->getentityManager()->getRepository($class);
    }
}
