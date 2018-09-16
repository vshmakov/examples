<?php

class DT extends DateTime
{
    public static function createFromFormat($format, $string, $o = null)
    {
        $dt = \DateTime::createFromFormat($format, $string);

        return ($dt && $dt->getTimestamp() > 0) ? static::createFromDT($dt) : false;
    }

    public static function start()
    {
        return static::createFromTimestamp(0);
    }

    public function createFromDbFormat($string)
    {
        return static::createFromFormat('Y-m-d H:i:s', $string);
    }

    public static function createFromDT($dt)
    {
        return ($dt instanceof \DateTimeInterface) ? static::createFromTimestamp($dt->getTimestamp()) : null;
    }

    public static function createBySub(string $intervalString)
    {
        return (new static())->sub(new \DateInterval($intervalString));
    }

    public static function createBySubDays($days)
    {
        return self::createBySub("P{$days}D");
    }

    public static function createByAdd(DateInterval $dateInterval)
    {
        return (new static())->add(new \DateInterval($dateInterval));
    }

    public static function createFromTimestamp($time)
    {
        $dt = new static();

        return $dt->setTimestamp($time);
    }

    public function dbFormat()
    {
        return $this->format('Y-m-d H:i:s');
    }

    public function stFormat()
    {
        return $this->format('d.m.Y H:i:s');
    }

    public function timeFormat()
    {
        return $this->format('H:i:s');
    }

    public function dateFormat()
    {
        return $this->format('d.m.Y');
    }

    public function setStartDay()
    {
        return $this->setTime(0, 0);
    }

    public function setFinishDay()
    {
        return $this->setTime(23, 59, 59);
    }

    public function __toString()
    {
        return $this->stFormat();
    }

    public function getRoundDays()
    {
        return round($this->getTimestamp() / DAY);
    }

    public function isPast(): bool
    {
        return $this->getTimestamp() < time();
    }

    public function getRoundUpDays()
    {
        $t = $this->getTimestamp();
        $d = (((int) ($t / DAY)));

        return 0 == $t % DAY ? $d : $d + 1;
    }

    public function minSecFormat()
    {
        return $this->format('i:s');
    }

    public function shDbFormat()
    {
        return $this->format('y-m-d H:i:s');
    }

    public function shOrdFormat()
    {
        return $this->format(sprintf('y-n-j G:%s:%s', $this->getMinutes(), $this->getSeconds()));
    }

    public function getMinutes()
    {
        return (int) ($this->getTimestamp() % HOUR / MIN);
    }

    public function getSeconds()
    {
        return $this->getTimeStamp() % MIN;
    }

    public function diff($dt, $absolute = null)
    {
        return DTI::createFromDTI(parent::diff($dt, $absolute));
    }

    public function getRoundTimestamp()
    {
        return round($this->format('U.u'));
    }
}
