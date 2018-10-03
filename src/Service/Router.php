<?php

namespace App\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Router
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function link($routeName, $parameters, $title)
    {
        return sprintf(
            '<a href="%s">%s</a>',
            $this->urlGenerator->generate($routeName, $parameters),
            $title
        );
    }
}
