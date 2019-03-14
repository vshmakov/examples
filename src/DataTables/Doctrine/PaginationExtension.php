<?php

namespace App\DataTables\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Controller\ApiPlatform\Doctrine\SystemExtensionInterface;
use App\Request\DataTables\DataTablesRequestProviderInterface;
use App\Request\Pagination\PaginationRequestProviderInterface;
use Doctrine\ORM\QueryBuilder;

final class PaginationExtension implements QueryCollectionExtensionInterface, SystemExtensionInterface
{
    /** @var PaginationRequestProviderInterface */
    private $paginationRequestProvider;

    public function __construct(DataTablesRequestProviderInterface $paginationRequestProvider)
    {
        $this->paginationRequestProvider = $paginationRequestProvider;
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null): void
    {
        $paginationRequest = $this->paginationRequestProvider->getPaginationRequest();
        $queryBuilder
            ->setFirstResult($paginationRequest->getStart())
            ->setMaxResults($paginationRequest->getLength());
    }
}
