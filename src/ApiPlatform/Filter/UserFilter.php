<?php

namespace App\ApiPlatform\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Attempt\EventSubscriber\FilterUserSubscriber;
use App\Entity\Attempt;
use Doctrine\ORM\QueryBuilder;

final class UserFilter extends AbstractContextAwareFilter
{
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null): void
    {
        if (Attempt::class !== $resourceClass or FilterUserSubscriber::FIELD !== $property or 'get' !== $operationName) {
            return;
        }

        $queryBuilder
            ->join(sprintf('%s.session', $queryBuilder->getRootAlias()), 's')
            ->join('s.user', 'u')
            ->andWhere('u.username = :username')
            ->setParameter('username', $value);
    }

    public function getDescription(string $resourceClass): array
    {
        return [];
    }
}
