<?php

namespace App\Object;

use Doctrine\Instantiator\Instantiator;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

abstract class ObjectAccessor
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

    /**
     * @return mixed|null
     */
    public static function getNullableTraversedValue($object_, string $property)
    {
        try {
            return self::getValue($object_, $property);
        } catch (UnexpectedTypeException $exception) {
            return null;
        }
    }

    private static function createPropertyAccessor(): PropertyAccessor
    {
        return PropertyAccess::createPropertyAccessorBuilder()
            ->enableExceptionOnInvalidIndex()
            ->getPropertyAccessor();
    }

    public static function initialize(string $class, array $data): object
    {
        $object = new  $class();
        self::setValues($object, $data);

        return $object;
    }

    public static function instantiate(string $class, array $data): object
    {
        $instantiator = new Instantiator();
        $object = $instantiator->instantiate($class);
        self::setValues($object, $data);

        return $object;
    }

    public static function setValues(object $object, array $values): void
    {
        foreach ($values as $key => $value) {
            self::setValue($object, $key, $value);
        }
    }

    /**
     * @param mixed $value
     */
    public static function setValue(object $object, string $key, $value): void
    {
        $propertyAccessor = self::createPropertyAccessor();
        $propertyAccessor->setValue($object, $key, $value);
    }
}
