<?php

namespace App\Tests\Unit;

use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

trait ServiceInitializerTrait
{
    private function initializeNormalizer(): NormalizerInterface
    {
        return new Serializer([new ObjectNormalizer()], []);
    }
}
