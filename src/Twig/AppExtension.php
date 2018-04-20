<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Service\UserLoader as UL;
use App\Repository\AttemptRepository as AttR;

class AppExtension extends AbstractExtension implements \Twig_Extension_GlobalsInterface
{
private $ul;
private $gl=[];

public function __construct (UL $ul, AttR $attR) {
$this->ul=$ul;
$this->gl=[
"ul"=>$ul,
"hasActualAttempt"=>!!$attR->findLastActualByCurrentUser(),
];
}

public function getGlobals() {
return $this->gl;
}
}
