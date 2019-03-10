<?php

namespace App\Serializer;

use Symfony\Component\Serializer\Encoder\EncoderInterface;

final class JsonDatatablesEncoder implements EncoderInterface
{
    public const FORMAT = 'jsondatatables';

    public function encode($data, $format, array $context = [])
    {
        return json_encode($data);
    }

    public function supportsEncoding($format): bool
    {
        return self::FORMAT === $format;
    }
}
