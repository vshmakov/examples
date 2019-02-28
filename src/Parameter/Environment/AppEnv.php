<?php

namespace App\Parameter\Environment;

use App\Parameter\ChooseInterface;

final class AppEnv implements ChooseInterface
{
    public const PROD = 'prod';
    public const DEV = 'dev';
    public const TEST = 'test';

    /** @var string */
    private $appEnv;

    public function __construct(string $appEnv)
    {
        $this->appEnv = $appEnv;
    }

    public function is(string $value): bool
    {
        return $value === $this->appEnv;
    }
}
