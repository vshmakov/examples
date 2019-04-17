<?php

namespace App\Serializer\Normalizer;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\ContextAwareFilterInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use App\ApiPlatform\Doctrine\SystemExtensionInterface;
use App\ApiPlatform\Format;
use App\Iterator;
use App\Request\DataTables\DataTablesRequestProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Iterable_;
use Psr\Container\ContainerInterface;
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

    /** @var Iterable_ */
    private $collectionFilters;

    public function __construct(
        ContainerInterface $container,
        ObjectNormalizer $normalizer,
        DataTablesRequestProviderInterface $dataTablesRequestProvider,
        EntityManagerInterface $entityManager,
        RequestStack $requestStack,
        iterable $collectionExtensions,
        iterable $collectionFilters
    ) {
        $this->container = $container;
        $this->normalizer = $normalizer;
        $this->dataTablesRequestProvider = $dataTablesRequestProvider;
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->collectionExtensions = $collectionExtensions;
        $this->collectionFilters = $collectionFilters;
    }

    public function normalize($collection, $format = null, array $context = []): array
    {
        Assert::isIterable($collection, 'jsondatatables format supports only collection operations.');
        $data = [];

        foreach ($collection as $item) {
            $data[] = $this->container
                ->get('serializer')
                ->normalize($item, null, $context);
        }

        $dataTablesRequest = $this->dataTablesRequestProvider->getDataTablesRequest();
        $totalRecordsCount = $this->getTotalRecordsCount();

        return [
            'recordsTotal' => $totalRecordsCount,
            'rows_count' => \count($data),
            'recordsFiltered' => $totalRecordsCount,
            'draw' => $dataTablesRequest->getDraw(),
            'data' => $data,
        ];
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return Format::JSONDT === $format;
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
            ->select('count(o)')
            ->from($resourceClass, 'o');

        /** @var QueryCollectionExtensionInterface $extension */
        foreach ($this->collectionExtensions as $extension) {
            Assert::isInstanceOf($extension, QueryCollectionExtensionInterface::class);

            if (!($extension instanceof SystemExtensionInterface)) {
                $extension->applyToCollection($queryBuilder, $queryNameGenerator, $resourceClass, $operationName);
            }
        }

        /** @var ContextAwareFilterInterface $filter */
        foreach (Iterator::uniqueClass($this->collectionFilters) as $filter) {
            Assert::isInstanceOf($filter, ContextAwareFilterInterface::class);
            $filter->apply($queryBuilder, $queryNameGenerator, $resourceClass, $operationName, ['filters' => $request->query->all()]);
        }

        return $queryBuilder
            ->getQuery()
            ->getSingleScalarResult();
    }
}
