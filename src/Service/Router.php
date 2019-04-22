<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Router
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function linkToRoute($routeName, $parameters, $title)
    {
        return $this->link($this->urlGenerator->generate($routeName, $parameters), $title);
    }

    public function link($uri, $title)
    {
        return sprintf(
            '<a href="%s">%s</a>',
$uri,
            $title
        );
    }
}
