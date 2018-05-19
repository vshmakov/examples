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

public function getRandEx($set) {
$sign=$this->sign($set);
$m=[1=>"add", "sub", "mult", "div"][$sign];
extract($this->$m($set));
$nums=["first"=>$a, "second"=>$b];
return (object) ($nums+["sign"=>$sign]);
}

private function sign($set) {
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
$to=((int) $divMin!=0) ? $divMax/$divMin: 0;
$c=mt_rand($minDiv, (int) $to);
$from=((int) $divMin!=0) ? $divMin : 1;
$to=($c!=0) ? $divMax/$c : 1;
$b=mt_rand($from, (int) $to);
$a=$c*$b;
return ["first"=>$a, "second"=>$b];
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
}