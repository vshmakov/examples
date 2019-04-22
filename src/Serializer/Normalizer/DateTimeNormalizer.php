<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class DateTimeNormalizer implements NormalizerInterface
{
    /**
     * @param \DateTimeInterface $dateTime
     */
    public function normalize($dateTime, $format = null, array $context = []): int
    {
        return $dateTime->getTimestamp();
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof \DateTimeInterface;
    }
}
