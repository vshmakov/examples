<?php

namespace App\Parameter\Environment;

use App\Parameter\StringInterface;

class AppName implements StringInterface
{
    /** @var string */
    private $appName;

    public function __construct(string $appName)
    {
        $this->appName = $appName;
    }

    public function toString(): string
    {
        return $this->appName;
    }
}
