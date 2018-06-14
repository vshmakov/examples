<?php

namespace App\Service;

use Goutte\Client;

class IpInformer {
public static function getInfoByIp($ip) {
$client=self::getClient();
$crawler = $client->request('GET', 
"http://api.db-ip.com/v2/free/".urlencode($ip));
$isIp=preg_match("#^(\d{1,3}\.?){4}$#", $ip);
$an=$isIp ? json_decode($client->getResponse()->getContent(), true) : ["errorCode"=>"IsNotIp"];
$r=[];
foreach (getArrByStr("errorCode ipAddress continentCode continentName countryCode countryName stateProv city") as $k) {
$r[$k]=$an[$k] ?? null;
}

$r["error"]=(bool) $r["errorCode"];
return $r+(array) $an;
}

private static function getClient() {
static $c;
return $c ??$c=new Client();
}
}