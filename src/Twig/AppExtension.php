<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Service\UserLoader as UL;
use App\Repository\AttemptRepository as AttR;
use App\Repository\UserRepository as UR;

class AppExtension extends AbstractExtension implements \Twig_Extension_GlobalsInterface
{
private $ul;
private $gl=[];

public function __construct (UL $ul, AttR $attR, UR $uR) {
try {
$hasAtt=!!$attR->findLastActualByCurrentUser();
} catch (\Exception $ex) {
$hasAtt=false;
}

$this->ul=$ul;
$this->gl=[
"user"=>$u=$ul->getUser()->setER($uR),
"hasActualAttempt"=>$hasAtt,
"PRICE"=>PRICE,
"isGuest"=>$ul->isGuest(),
"FEEDBACK_EMAIL"=>"post@exmasters.ru",
];
}

public function getGlobals() {
return $this->gl;
}

public function getFunctions() {
return [
new TwigFunction("addTimeNumber", [$this, "getAddTimeNumber"]),
new TwigFunction("sortByAddTime", [$this, "sortByAddTime"]),
new TwigFunction("sortProfiles", [$this, "sortProfiles"]),
];
}

public function getAddTimeNumber($v, array $ents) {
$dt=$v->getAddTime();
$n=count($ents);

foreach ($ents as $e) {
if ($dt->getTimestamp() < $e->getAddTime()->getTimestamp()) $n--;
}

return $n;
}

public function sortByAddTime($ents) {
usort($ents, function ($e1, $e2) {
$t1=$e1->getAddTime()->getTimestamp();
$t2=$e2->getAddTime()->getTimestamp();

if ($t1 == $t2) return 0;
return $t1 < $t2 ? -1 : 1;
});
return $ents;
}

public function sortProfiles($ps) {
$cp=$this->ul->getUser()->getCurrentProfile();
$ps=$this->sortByAddTime($ps);
usort($ps, function ($e1, $e2) use ($cp) {
if ($cp === $e1) return  -1;
if ($cp === $e2) return  1;
return 0;
});
return $ps;
}
}