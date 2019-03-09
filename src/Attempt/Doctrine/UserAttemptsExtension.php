<?php

namespace App\Attempt\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Attempt;
use App\Security\User\CurrentUserProviderInterface;
use Doctrine\ORM\QueryBuilder;

final class UserAttemptsExtension implements QueryCollectionExtensionInterface
{
    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;

    public function __construct(CurrentUserProviderInterface $currentUserProvider)
    {
        $this->currentUserProvider = $currentUserProvider;
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if (Attempt::class !== $resourceClass or 'get_user_attempts' !== $operationName) {
            return;
        }

        $attemptAlias = $queryBuilder->getRootAlias();
        $queryBuilder
            ->join(sprintf('%s.session', $attemptAlias), 's')
            ->andWhere('s.user = :user')
            ->setParameter('user', $this->currentUserProvider->getCurrentUserOrGuest());
    }
}
