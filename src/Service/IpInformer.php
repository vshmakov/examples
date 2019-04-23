<?php

namespace App\Service;

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

            $answer = $isIp ? json_decode($json, true) : ['errorCode' => 'IsNotIp'];
            $info = [];

            foreach (arr('errorCode ipAddress continentCode continentName countryCode countryName stateProv city') as $property) {
                $info[$property] = $answer[$property] ?? null;
            }

            $info['error'] = (bool) $info['errorCode'];
            $ips[$ip] = $info + (array) $answer;
        }

        return $ips[$ip];
    }

    public static function isIp($ip)
    {
        return preg_match("#^(\d{1,3}\.?){4}$#", $ip);
    }
}
