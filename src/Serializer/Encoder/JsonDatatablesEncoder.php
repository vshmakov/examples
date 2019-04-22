<?php

declare(strict_types=1);

namespace App\Serializer\Encoder;

use App\ApiPlatform\Format;
use App\Parameter\ChooseInterface;
use App\Parameter\Environment\AppEnv;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

final class JsonDatatablesEncoder implements EncoderInterface
{
    /** @var ChooseInterface */
    private $applicationEnvironment;

    public function __construct(ChooseInterface $applicationEnvironment)
    {
        $this->applicationEnvironment = $applicationEnvironment;
    }

    public function encode($data, $format, array $context = [])
    {
        $options = JSON_UNESCAPED_UNICODE;

        if ($this->applicationEnvironment->is(AppEnv::DEV)) {
            $options = $options | JSON_PRETTY_PRINT;
        }

        return json_encode($data, $options);
    }

    public function supportsEncoding($format): bool
    {
        return Format::JSONDT === $format;
    }
}
