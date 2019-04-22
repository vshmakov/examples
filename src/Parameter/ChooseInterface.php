<?php

declare(strict_types=1);

namespace App\Parameter;

interface ChooseInterface
{
    public function is(string $value): bool;
}
