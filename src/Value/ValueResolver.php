<?php

namespace App\Value;

use Webmozart\Assert\Assert;

abstract class ValueResolver
{
    public static function greaterThan(float $value, float $minLimit): float
    {
        return $value >= $minLimit ? $value : $minLimit;
    }

    public static function lessThan(float $value, float $maxLimit): float
    {
        return $value <= $maxLimit ? $value : $maxLimit;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function between(float $minLimit, float $maxLimit, float $value): float
    {
        Assert::greaterThan($maxLimit, $minLimit);

        return self::lessThan(self::greaterThan($value, $minLimit), $maxLimit);
    }
}
