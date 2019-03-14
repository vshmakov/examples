<?php

namespace App\Serializer\Normalizer;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use App\Controller\ApiPlatform\Doctrine\SystemExtensionInterface;
use App\Request\DataTables\DataTablesRequestProviderInterface;
use App\Serializer\JsonDatatablesEncoder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Webmozart\Assert\Assert;

final class DataTablesNormalizer implements NormalizerInterface
{
    /** @var ObjectNormalizer */
    private $normalizer;

    /** @var DataTablesRequestProviderInterface */
    private $dataTablesRequestProvider;

    /** @var EntityManagerInterface */
    private $entityManager;
    private $requestStack;

    /** @var iterable */
    private $collectionExtensions;

    public function __construct(
        ObjectNormalizer $normalizer,
        DataTablesRequestProviderInterface $dataTablesRequestProvider,
        EntityManagerInterface $entityManager,
        RequestStack $requestStack,
        iterable $collectionExtensions
    ) {
        $this->normalizer = $normalizer;
        $this->dataTablesRequestProvider = $dataTablesRequestProvider;
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->collectionExtensions = $collectionExtensions;
    }

    public function normalize($collection, $format = null, array $context = []): array
    {
        Assert::isIterable($collection, 'jsondatatables format supports only collection operations.');
        $data = [];

        foreach ($collection as $item) {
            $data[] = $this->normalizer->normalize($item, null, $context);
        }

        $dataTablesRequest = $this->dataTablesRequestProvider->getDataTablesRequest();
        $totalRecordsCount = $this->getTotalRecordsCount();

        return [
            'draw' => $dataTablesRequest->getDraw(),
            'recordsTotal' => $totalRecordsCount,
            'recordsFiltered' => $totalRecordsCount,
            'data' => $data,
        ];
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return JsonDatatablesEncoder::FORMAT === $format;
    }

    private function getTotalRecordsCount(): int
    {
        $request = $this->requestStack->getMasterRequest();
        Assert::notNull($request);
        $queryNameGenerator = new QueryNameGenerator();
        $resourceClass = $request->attributes->get('_api_resource_class');
        $operationName = $request->attributes->get('_api_collection_operation_name');
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('count(t)')
            ->from($resourceClass, 't');

        /** @var QueryCollectionExtensionInterface $extension */
        foreach ($this->collectionExtensions as $extension) {
            if (!($extension instanceof SystemExtensionInterface)) {
                $extension->applyToCollection($queryBuilder, $queryNameGenerator, $resourceClass, $operationName);
            }
        }

        return $queryBuilder
            ->getQuery()
            ->getSingleScalarResult();
    }
}
