<?php

namespace App\Parameter\Container;

interface ParametersContainerInterface
{
    public function setParameters(array $parameters): void;

    public function getParameters(): array;
}
