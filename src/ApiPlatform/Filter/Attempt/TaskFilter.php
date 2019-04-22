<?php

declare(strict_types=1);

namespace App\ApiPlatform\Filter\Attempt;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\ApiPlatform\Filter\BaseFilter;
use App\ApiPlatform\Filter\Validation\FilterTaskValidationSubscriber;
use App\Entity\Attempt;
use Doctrine\ORM\QueryBuilder;

final class TaskFilter extends BaseFilter
{
    protected function supports(string $property, string $resourceClass, ?string $operationName): bool
    {
        return Attempt::class === $resourceClass && FilterTaskValidationSubscriber::FIELD === $property && 'get' === $operationName;
    }

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null): void
    {
        $queryBuilder
            ->join(sprintf('%s.task', $queryBuilder->getRootAlias()), 't')
            ->andWhere('t.id = :taskId')
            ->setParameter('taskId', $value);
    }
}
