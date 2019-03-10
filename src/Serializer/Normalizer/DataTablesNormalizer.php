<?php

namespace App\Serializer\Normalizer;

use App\Request\DataTables\DataTablesRequestProviderInterface;
use App\Serializer\JsonDatatablesEncoder;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Webmozart\Assert\Assert;

final class DataTablesNormalizer implements NormalizerInterface
{
    /** @var ObjectNormalizer */
    private $normalizer;

    /** @var DataTablesRequestProviderInterface */
    private $dataTablesRequestProvider;

    public function __construct(ObjectNormalizer $normalizer, DataTablesRequestProviderInterface $dataTablesRequestProvider)
    {
        $this->normalizer = $normalizer;
        $this->dataTablesRequestProvider = $dataTablesRequestProvider;
    }

    public function normalize($array, $format = null, array $context = []): array
    {
        Assert::isArray($array, 'jsondatatables format supports only collection operations.');
        Assert::true($this->dataTablesRequestProvider->hasDataTablesRequest(), 'DataTables request is not valid.');
        $data = [];

        foreach ($array as $item) {
            $data[] = $this->normalizer->normalize($item, null, $context);
        }

        $dataTablesRequest = $this->dataTablesRequestProvider->getDataTablesRequest();
        $dataCount = \count($data);

        return [
            'draw' => $dataTablesRequest->getDraw(),
            'recordsTotal' => $dataCount,
            'recordsFiltered' => $dataCount,
            'data' => $data,
        ];
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return JsonDatatablesEncoder::FORMAT === $format;
    }
}
