<?php

namespace App\Twig;

use Twig\TwigFunction;

trait BaseTrait
{
    private function prepareFunctions($functions)
    {
        return array_map(
            function (string $function): TwigFunction {
                return new TwigFunction($function, [$this, $function]);
            },
            $functions
        );
    }
}
