<?php

namespace App\Repository\Traits;

use Doctrine\ORM\Query;

trait QueryResultTrait
{
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
}
