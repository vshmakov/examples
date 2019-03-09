<?php

namespace App\Api;

use ApiPlatform\Core\DataProvider\PaginatorInterface;

trait PaginatorToArrayTrait
{
    private function paginatorToArray(PaginatorInterface $paginator): array
    {
        $array = [];

        foreach ($paginator as $item) {
            $array[] = $item;
        }

        return $array;
    }
}
