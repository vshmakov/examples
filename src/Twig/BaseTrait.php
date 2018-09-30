<?php

namespace App\Twig;

use Twig\TwigFunction;

trait BaseTrait
{
    protected function prepareFunctions($functions)
    {
        array_map(
            function ($function) {
                return new TwigFunction($function, [$this, $function]);
            },
            $functions
        );
    }
}
