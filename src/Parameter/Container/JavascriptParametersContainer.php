<?php

namespace App\Parameter\Container;

final class JavascriptParametersContainer implements ParametersContainerInterface
{
    /** @var array */
    private $parameters = [];

    public function setParameters(array $parameters): void
    {
        $this->parameters += $parameters;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
