<?php

namespace App\Attempt\Example;

use App\Entity\Example;
use App\Entity\Settings;

interface ExampleGeneratorInterface
{
    public function generate(Settings $settings): Example;
}
