<?php

namespace App\Serializer\Normalizer;

use App\Response\AttemptResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

final class AttemptResponseNormalizer implements NormalizerInterface
{
    /** @var ObjectNormalizer */
    private $normalizer;

    public function __construct(ObjectNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * @param AttemptResponse $object
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        $data['limitTime'] = $object->getLimitTime()->getTimestamp();
        $data['isFinished'] = $object->isFinished();
        unset($data['finished']);

        return $data;
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof AttemptResponse;
    }
}
