<?php

namespace App\ApiPlatform\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

abstract class BaseFilter extends AbstractContextAwareFilter
{
    final public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null, array $context = []): void
    {
        if (!isset($context['filters']) || !\is_array($context['filters'])) {
            parent::apply($queryBuilder, $queryNameGenerator, $resourceClass, $operationName, $context);

            return;
        }

        foreach ($context['filters'] as $property => $value) {
            if ($this->supports($property, $resourceClass, $operationName)) {
                $this->filterProperty($property, $value, $queryBuilder, $queryNameGenerator, $resourceClass, $operationName, $context);
            }
        }
    }

    final public function getDescription(string $resourceClass): array
    {
        return [];
    }

    abstract protected function supports(string $property, string $resourceClass, ?string $operationName): bool;

    abstract protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null): void;
}
