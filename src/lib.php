<?php

use App\DT;

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