<?php

declare(strict_types=1);

namespace App;

abstract class Iterator
{
    public static function map(iterable $iterator, callable $callback): array
    {
        $result = [];

        foreach ($iterator as $item) {
            $result[] = $callback($item);
        }

        return $result;
    }

    public static function uniqueClass(iterable $collection): array
    {
        $uniqueItems = [];

        foreach ($collection as $item) {
            $className = \get_class($item);

            if (!isset($uniqueItems[$className])) {
                $uniqueItems[$className] = $item;
            }
        }

        return $uniqueItems;
    }
}
