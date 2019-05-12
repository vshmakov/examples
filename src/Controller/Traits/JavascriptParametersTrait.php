<?php

namespace App\Controller\Traits;

use App\Parameter\Container\ParametersContainerInterface;

trait JavascriptParametersTrait
{
    private function setJavascriptParameters(array $parameters): void
    {
        $this->container
            ->get(ParametersContainerInterface::class)
            ->setParameters($parameters);
    }
}
