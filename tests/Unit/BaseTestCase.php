<?php

namespace App\Tests\Unit;

use App\Tests\DataProviderTrait;
use PHPUnit\Framework\TestCase;

abstract class BaseTestCase extends TestCase
{
    use DataProviderTrait;
}
