<?php

namespace App\Service;

class ExampleManager {
public static function solve(float $a, float  $b, int $s) {
switch ($s) {
case 1:
return $a+$b;
break;
case 2:
return $a-$b;
break;
case 3:
return $a*$b;
break;
case 4:
return ($b) ? $a/$b : false;
break;
}
}

public static function rating($c, $e) {
$r=$c-abs($e);
$x=[];

for ($i=5; $i>=1; $i--) {
$prev=$i == 5 ? $c : $x[$i+1];
$p=$i == 5 ? 0.98 : 0.97;
if ($c <=50) $p=$i == 5 ? 0.96 : 0.94;
if ($c <=30) $p=$i == 5 ? 0.97 : 0.92;
if ($c  <= 15) $p=$i == 5 ? 0.94 : 0.88;
if ($c  <= 9) $p=$i == 5 ? 1 : 0.85;

$x[$i]=abs((int) ($prev*$p));
}

$o=1;
for ($i=1; $i<=5; $i++) {
if ($r >= $x[$i]) $o=$i;
}

return $o;
}

public function getRandEx($sign, $set, $prevs) {
$m=$this->actName($sign);
$k=0;
$anK=prob(80);
$deltK=prob(70);

for ($i=1; $i<=20; $i++) {
extract($this->$m($set));
$as=$this->assess($a, $b, $sign, $set, $prevs, $anK, $deltK);
if ($as > $k) {
$k=$as;
$nums=["first"=>$a, "second"=>$b];
}
}

return (object) ($nums+["sign"=>$sign]);
}

private function assess($a, $b, $sign, $set, $prevs, $anK, $deltK) {
$k=100;
$rk=$sk=$dk=0;
$ec=count($prevs) ?: 1;

foreach ($prevs as $p) {
if ($p->getFirst() == $a && $p->getSecond() == $b && $p->getSign() == $sign) {
$rk=1/$ec*60;
}

if ((self::solve($a, $b, $sign) == $p->getAnswer()) && $anK) {
$sk+=1/$ec*30;
}
}

if ($deltK) {
$dk=($this->dist($a, $b, $sign, $set)*10/100/($ec**0.2));
}

$k-=$rk+$sk+$dk;

return $k;
}

private function dist($a, $b, $sign, $set) {
$act=($this->actName($sign));
foreach (["F", "S", ""] as $n) {
foreach (["Min", "Max"] as $m) {
$v=lcfirst($n.$m);
$$v=$set[$act.$n.$m];
}
}

$k=0;
$k+=distPerc($a, $fMin, $fMax);
$k+=distPerc($b, $sMin, $sMax);
$an=self::solve($a, $b, $sign);
//$k+=distPerc($an, $min, $max);
$k=round(($k/3)**0.7);

return $k;
}

private function actName($sign) {
return [1=>"add", "sub", "mult", "div"][$sign];
}

public function getRandSign($set) {
$rand=mt_rand(1, 100);
$k=0;
$sign=1;

foreach([1=>"addPerc", "subPerc", "multPerc", "divPerc"] as $s=>$p) {
$k+=$set[$p];
if ($rand<=$k) {
$sign=$s;
break;
}
}

return $sign;
}

private function add($set) {
extract($set);
$k=(bool) mt_rand(0, 1);

if ($k) {
$f1=btwVal($addFMin, $addFMax, $addMin-$addSMax);
$t1=btwVal($addFMin, $addFMax, $addMax-$addSMin);
$a=mt_rand($f1, $t1);

$f2=btwVal($addSMin, $addSMax, $addMin-$a);
$t2=btwVal($addSMin, $addSMax, $addMax-$a);
$b=mt_rand($f2, $t2);
} else {
$f2=btwVal($addSMin, $addSMax, $addMin-$addFMax);
$t2=btwVal($addSMin, $addSMax, $addMax-$addFMin);
$b=mt_rand($f2, $t2);

$f1=btwVal($addFMin, $addFMax, $addMin-$b);
$t1=btwVal($addFMin, $addFMax, $addMax-$b);
$a=mt_rand($f1, $t1);
}

return ["a"=>$a, "b"=>$b];
}

private function sub($set) {
extract($set);
extract($this->add([
"addFMin"=>$subMin,
"addFMax"=>$subMax,
"addSMin"=>$subSMin,
"addSMax"=>$subSMax,
"addMin"=>$subFMin,
"addMax"=>$subFMax,
]));

return ["a"=>$a+$b, "b"=>$b];
}

private function mult($set) {
extract($set);
$k=(bool) mt_rand(0, 1);

if ($k) {
$f1=btwVal($multFMin, $multFMax, $multMin / ($multSMax ?: 1));
$t1=btwVal($multFMin, $multFMax, $multMax / ($multSMin ?: 1));
$a=mt_rand($f1, $t1);

$f2=btwVal($multSMin, $multSMax, $multMin / ($a ?: 1));
$t2=btwVal($multSMin, $multSMax, $multMax / ($a ?: 1));
$b=mt_rand($f2, $t2);
} else {
$f2=btwVal($multSMin, $multSMax, $multMin / ($multFMax ?: 1));
$t2=btwVal($multSMin, $multSMax, $multMax/ ($multFMin ?: 1));
$b=mt_rand($f2, $t2);

$f1=btwVal($multFMin, $multFMax, $multMin/ ($b ?: 1));
$t1=btwVal($multFMin, $multFMax, $multMax / ($b ?: 1));
$a=mt_rand($f1, $t1);
}

return ["a"=>$a, "b"=>$b];
}

private function div($set) {
extract($set);
extract($this->mult([
"multFMin"=>$divMin,
"multFMax"=>$divMax,
"multSMin"=>$divSMin,
"multSMax"=>$divSMax,
"multMin"=>$divFMin,
"multMax"=>$divFMax,
]));

return ["a"=>$a * $b, "b"=>$b];
}

}