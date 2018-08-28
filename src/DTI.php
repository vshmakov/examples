<?php

namespace App;

class DTI extends \DateInterval
{
    public function getTimestamp()
    {
        return self::DTIToTimestamp($this);
    }

    public function toDT()
    {
        return self::DTIToDT($this);
    }

    public static function createFromDTI($dti)
    {
        $r = new self('PT0S');

        foreach ($dti as $k => $v) {
            $r->$k = $v;
        }

        return $r;
    }

    public static function createFromTimestamp($s)
    {
        $dti = DT::start()->diff(DT::createFromTimestamp($s));

        return self::createFromDTI($dti);
    }

    public static function DTIToDT($dti)
    {
        return DT::start()->add($dti);
    }

    public static function DTIToTimestamp($dti)
    {
        return self::DTIToDT($dti)->getRoundTimestamp();
    }
}
