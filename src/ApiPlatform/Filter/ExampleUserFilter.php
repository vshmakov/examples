<?php

namespace App\ApiPlatform\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Attempt\EventSubscriber\FilterUserSubscriber;
use App\Entity\Example;
use Doctrine\ORM\QueryBuilder;

final class ExampleUserFilter extends AbstractContextAwareFilter
{
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null): void
    {
        if (Example::class !== $resourceClass or FilterUserSubscriber::FIELD !== $property or 'get' !== $operationName) {
            return;
        }

        $queryBuilder
            ->join(sprintf('%s.attempt', $queryBuilder->getRootAlias()), 'a')
            ->join('a.session', 's')
            ->join('s.user', 'u')
            ->andWhere('u.username = :username')
            ->setParameter('username', $value);
    }

    public function getDescription(string $resourceClass): array
    {
        return [];
    }
}
