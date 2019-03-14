<?php

namespace App\Serializer\Encoder;

use App\Parameter\Api\Format;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

final class JsonDatatablesEncoder implements EncoderInterface
{
    public function encode($data, $format, array $context = [])
    {
        return json_encode($data);
    }

    public function supportsEncoding($format): bool
    {
        return Format::JSONDT === $format;
    }
}
