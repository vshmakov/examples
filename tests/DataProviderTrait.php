<?php

namespace App\Tests;

trait DataProviderTrait
{
    protected function wrapItemsInArray(array $items): array
    {
        return array_map(
            function ($item): array {
                return [$item];
            },
            $items
        );
    }
}
