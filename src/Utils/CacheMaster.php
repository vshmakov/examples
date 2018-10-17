<?php

namespace App\Utils;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\PropertyAccess\PropertyAccess;

class CacheMaster
{
    private static $cache;

    public function __construct()
    {
        self::initCache();
    }

    private static function initCache()
    {
        if (!self::$cache) {
            self::$cache = new ArrayCollection;
        }
    }

    public function get($key, callable $callback = null)
    {
        if (is_array($key)) {
            $key = $this->generateKey(...$key);
        }

            return self::$cache->get($key) ?? $this->set($key, $callback());
    }

    public function set(string $key, $value)
    {
        self::$cache->set($key, $value);

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
}