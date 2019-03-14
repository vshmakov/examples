<?php

namespace App\Parameter\Container;

final class JavascriptParametersContainer implements ParametersContainerInterface
{
    /** @var array */
    private static $parameters = [];

    public function setParameters(array $parameters): void
    {
        self::$parameters = $parameters;
    }

    public function getParameters(): array
    {
        return self::$parameters;
    }
}
