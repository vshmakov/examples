<?php

namespace App;

abstract class Iterator
{
    public static function map(iterable $iterator, callable $callback): array
    {
        $result = [];

        foreach ($iterator as $key => $item) {
            $result[] = $callback($item);
        }

        return $result;
    }
}
