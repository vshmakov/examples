<?php

declare(strict_types=1);

namespace App\Parameter\Container;

interface ParametersContainerInterface
{
    public function setParameters(array $parameters): void;

    public function getParameters(): array;
}
