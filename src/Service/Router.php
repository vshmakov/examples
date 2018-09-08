<?php

namespace App\Service;

use   Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Router
{
    private $r;

    public function __construct(UrlGeneratorInterface $r)
    {
        $this->r = $r;
    }

    public function link($r, $p, $t)
    {
        return sprintf('<a href="%s">%s</a>', $this->r->generate($r, $p), $t);
    }
}
