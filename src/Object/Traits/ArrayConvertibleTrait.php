<?php

namespace App\Object\Traits;

use App\Object\ObjectAccessor;

trait ArrayConvertibleTrait
{
    public function toArray(): array
    {
        return ObjectAccessor::getValues($this, self::getKeys());
    }

    abstract public static function getKeys(): array;
}
