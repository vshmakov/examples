<?php

namespace App\Parameter;

interface ChooseInterface
{
    public function is(string $value): bool;
}
