<?php

use App\DT;

define("PRICE", 97);

function dt($dt) {
return DT::createFromDT($dt);
}

call_user_func(function () {
$min=60;
$hour=60*$min;
$day=24*$hour;
$toUtc=-3*$hour;

foreach(array(
'MIN'=>$min,
'HOUR'=>$hour,
'DAY'=>$day,
'MONTH'=>30*$day,
'TO_UTC'=>$toUtc,
) as $key=>$val) {
define($key, $val);
}
});

function normPerc($p) {
$all1=0;
foreach ($p as $k=>$v) {
$all1+=abs($v);
}

if (!$all1) $all1=1;
$all2=0;
foreach ($p as $key=>$val) {
$all2+=$p[$key]=round($val/$all1*100);
}

foreach (array_reverse($p) as $k=>$v) {
if ($v) {
$p[$k]+=100-$all2;
return $p;
}
}

$p[$k]+=100-$all2;
return $p;
}

function getArrByKeies($arr, $ka) {
$res=[];

foreach ($ka as $k) {
if (isset($arr[$k])) $res[$k]=$arr[$k];
}

return ($res);
}

function getArrByStr($s) {
$a=explode(" ", $s);
return $a;
}

function getMethodName($s, $p="") {
return $p.ucfirst($s);
}

function entityGetter($v) {
return preg_match("#^get[A-Z]#", $v) ? $v : "get".ucfirst($v);
}

function getKeiesFromEntity($s, $e) {
$d=[];

foreach ((getArrByStr($s)) as $k) {
$m=entityGetter($k);
$d[$k]=$e->$m();
}

return $d;
}

function _log(...$attr) {
file_put_contents(__dir__."/log.log", json_encode($attr));
}

function createNumArr($a) {
$rn=[];
foreach ($a as $v) {
$rn[]=$v;
}
return $rn;
}