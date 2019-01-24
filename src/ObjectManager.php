<?php

namespace App;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

abstract class ObjectManager
{
    public static function getValues($objectOrArray, array $fields): array
    {
        $values = [];

        foreach ($fields as $field) {
            $values[$field] = self::getValue($objectOrArray, $field);
        }

        return $values;
    }

    public static function getValue($objectOrArray, string $field)
    {
        return self::createPropertyAccessor()->getValue($objectOrArray, $field);
    }

    private static function createPropertyAccessor(): PropertyAccessor
    {
        return PropertyAccess::createPropertyAccessorBuilder()
            ->enableExceptionOnInvalidIndex()
            ->getPropertyAccessor();
    }
}
