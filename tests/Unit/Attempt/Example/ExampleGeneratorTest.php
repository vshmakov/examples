<?php

namespace App\Tests\Unit\Attempt\Example;

use App\Tests\Unit\ServiceInitializerTrait;
use PHPUnit\Framework\TestCase;

final class ExampleGeneratorTest extends TestCase
{
    use ServiceInitializerTrait;
    private static $f;

    /**
     * @test
     */
    public function serviceGeneratesAdditionExamples(): void
    {
    }
}
