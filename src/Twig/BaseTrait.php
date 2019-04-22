<?php

declare(strict_types=1);

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
