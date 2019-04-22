<?php

declare(strict_types=1);

namespace App\Tests\Unit\Twig;

use App\Twig\AppExtension;
use Doctrine\Instantiator\Instantiator;
use PHPUnit\Framework\TestCase;

final class AppExtensionTest extends TestCase
{
    /**
     * @test
     */
    public function toLabelStringFilterReturnsrightFormat(): void
    {
        $instantiator = new Instantiator();
        /** @var AppExtension $appExtension */
        $appExtension = $instantiator->instantiate(AppExtension::class);

        $this->assertSame('Label string', $appExtension->toLabelStringFilter('labelString'));
    }
}
