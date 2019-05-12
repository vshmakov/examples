<?php

namespace App\Service;

final class IpInformer
{
    public static function isIp($ip)
    {
        return preg_match("#^(\d{1,3}\.?){4}$#", $ip);
    }
}
