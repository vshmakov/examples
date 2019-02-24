<?php

namespace App\Tests\Unit\DataNormalizer\Settings;

use App\DataNormalizer\Settings\SolveSettingsNormalizer;
use App\Entity\Settings;
use App\Object\ObjectAccessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class SettingsNormalizerTest extends TestCase
{
    /**
     * @test
     */
    public function testSomething(): void
    {
        $normalizer = new Serializer([new ObjectNormalizer()], []);
        /** @var Settings $settings */
        $settings = ObjectAccessor::initialize(Settings::class, [
            'addFMin' => 5,
            'addFMax' => 3,
            'addSMin' => 5,
            'addMin' => 8,
        ]);
        $solveSettingsNormalizer = new SolveSettingsNormalizer($normalizer);

        $solveSettingsNormalizer->normalize($settings);
        $this->assertSame(5, $settings->getAddFMax());
        $this->assertSame(10, $settings->getAddMin());
    }
}
