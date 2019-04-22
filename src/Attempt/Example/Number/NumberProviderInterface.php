<?php

declare(strict_types=1);

namespace App\Attempt\Example\Number;

use App\Entity\Example;

interface NumberProviderInterface
{
    public function getNumber(Example $example): int;
}
