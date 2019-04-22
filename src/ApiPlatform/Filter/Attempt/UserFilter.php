<?php

declare(strict_types=1);

namespace App\ApiPlatform\Filter\Attempt;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\ApiPlatform\Filter\BaseFilter;
use App\ApiPlatform\Filter\Validation\FilterUserValidationSubscriber;
use App\Entity\Attempt;
use Doctrine\ORM\QueryBuilder;

final class UserFilter extends BaseFilter
{
    protected function supports(string $property, string $resourceClass, ?string $operationName): bool
    {
        return Attempt::class === $resourceClass && FilterUserValidationSubscriber::FIELD === $property && 'get' === $operationName;
    }

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null): void
    {
        $queryBuilder
            ->join(sprintf('%s.session', $queryBuilder->getRootAlias()), 's')
            ->join('s.user', 'u')
            ->andWhere('u.username = :username')
            ->setParameter('username', $value);
    }
}
