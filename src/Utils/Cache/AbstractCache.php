<?php

namespace App\Utils\Cache;

use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class AbstractCache
{
    protected $storage;

    public function get($key, callable $callback)
    {
        $key = $this->processKey($key);

        if ($this->has($key)) {
            return $this->storage->get($key);
        }

        return $this->set($key, $callback());
    }

    abstract public function has($key) : bool;

    public function set($key, $value)
    {
        $this->storage->set($key, $value);

        return $value;
    }

    public function generateKey(...$parameters) : string
    {
        $propertyAccessor = $this->get('cache_master.generate_key.property_accessor', function () {
            return PropertyAccess::createPropertyAccessorBuilder()
                ->enableExceptionOnInvalidIndex()
                ->getPropertyAccessor();
        });
        $format = [array_shift(($parameters))];
        $sprintfParameters = array_reduce($parameters, function ($parameters, $object) use ($propertyAccessor) : array {
            $parameters[] = $propertyAccessor->getValue($object, 'id');

            return $parameters;
        }, $format);

        return sprintf(...$sprintfParameters);
    }

    protected function processKey($key) : string
    {
        return is_array($key) ? $this->generateKey(...$key) : $key;
    }
}
