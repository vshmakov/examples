<?php

namespace App\Tests\Unit;

use Doctrine\Common\Inflector\Inflector;
use PHPUnit\Framework\TestCase;

/**
 * tests that Inflector is available and works correctly.
 * Documentation  does  not recommend to use it in application code.
 * But we do it and must be sure that it is still here.
 *
 * @see https://github.com/symfony/inflector
 */
final class InflectorIsAvailableTest extends TestCase
{
    /**
     * @test
     */
    public function inflectorReturnsRightValues(): void
    {
        $this->assertSame('snake_cased_string', Inflector::tableize('snakeCasedString'));
        $this->assertSame('camelizeString', Inflector::camelize('camelize_string'));
    }
}
