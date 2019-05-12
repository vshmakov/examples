<?php

namespace App\ApiPlatform\Filter\Example;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\ApiPlatform\Filter\BaseFilter;
use App\ApiPlatform\Filter\Validation\FilterTaskValidationSubscriber;
use App\Entity\Example;
use Doctrine\ORM\QueryBuilder;

final class TaskFilter extends BaseFilter
{
    protected function supports(string $property, string $resourceClass, ?string $operationName): bool
    {
        return Example::class === $resourceClass && FilterTaskValidationSubscriber::FIELD === $property && 'get' === $operationName;
    }

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null): void
    {
        $attemptAssociation = $queryNameGenerator->generateJoinAlias('attempt');

        $queryBuilder
            ->join(sprintf('%s.attempt', $queryBuilder->getRootAlias()), $attemptAssociation)
            ->join(sprintf('%s.task', $attemptAssociation), 't')
            ->andWhere('t.id = :taskId')
            ->setParameter('taskId', $value);
    }
}
