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
];
}

public function getGlobals() {
return $this->gl;
}
}
