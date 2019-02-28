<?php

namespace App\DateTime;

use App\Object\ObjectAccessor;

final class DateInterval extends \DateInterval
{
    public static function createFromDateInterval(\DateInterval $interval): self
    {
        return ObjectAccessor::instantiate(
            self::class,
            ObjectAccessor::getValues($interval, ['f', 's', 'i', 'h', 'd', 'm', 'y', 'days', 'invert'])
        );
    }

    public static function createFromDateIntervalString(string $interval): self
    {
        return self::createFromDateInterval(new  \DateInterval($interval));
    }

    public static function createNormalizedFromDateIntervalString(string $interval): self
    {
        $intervalDate = \DT::createFromDateInterval(new \DateInterval($interval));
        $normalizedInterval = \DT::createByStart()->diff($intervalDate);

        return self::createFromDateInterval($normalizedInterval);
    }

    public function isBetween(\DateInterval $minimum, \DateInterval $maximum): bool
    {
        return $this->getTimestamp() >= self::createFromDateInterval($minimum)->getTimestamp()
            && $this->getTimestamp() <= self::createFromDateInterval($maximum)->getTimestamp();
    }

    private function getTimestamp(): int
    {
        return \DT::createFromDateInterval($this)->getTimestamp() - \DT::createByStart()->getTimestamp();
    }
}
