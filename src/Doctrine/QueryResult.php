<?php

declare(strict_types=1);

namespace App\Doctrine;

use Doctrine\ORM\Query;

abstract class QueryResult
{
    public static function column(Query $query): array
    {
        $result = [];

        foreach ($query->getArrayResult() as $row) {
            foreach ($row as $value) {
                $result[] = $value;
                break;
            }
        }

        return $result;
    }

    public static function value(Query $query)
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
