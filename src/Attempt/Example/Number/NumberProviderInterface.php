<?php

namespace App\Attempt\Example\Number;

use App\Entity\Example;

interface NumberProviderInterface
{
    public function getNumber(Example $example): int;
}
