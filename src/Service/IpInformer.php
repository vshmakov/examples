<?php

namespace App\Service;

use Goutte\Client;

class IpInformer
{
    public static function getInfoByIp($ip)
    {
        static $ips = [];
        $isIp = self::isIp($ip);

        if (!isset($ips[$ip])) {
            if ($isIp) {
                $json = file_get_contents('http://api.db-ip.com/v2/free/'.urlencode($ip));
            }

            $an = $isIp ? json_decode($json, true) : ['errorCode' => 'IsNotIp'];
            $r = [];

            foreach (getArrByStr('errorCode ipAddress continentCode continentName countryCode countryName stateProv city') as $k) {
                $r[$k] = $an[$k] ?? null;
            }

            $r['error'] = (bool) $r['errorCode'];
            $ips[$ip] = $r + (array) $an;
        }

        return $ips[$ip];
    }

    private static function getClient()
    {
        static $c;

        return $c ?? $c = new Client();
    }

    public static function isIp($ip)
    {
        return preg_match("#^(\d{1,3}\.?){4}$#", $ip);
    }
}
