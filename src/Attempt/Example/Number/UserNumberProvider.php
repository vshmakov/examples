<?php

declare(strict_types=1);

namespace App\Attempt\Example\Number;

use App\Entity\Example;
use Doctrine\ORM\EntityManagerInterface;

final class UserNumberProvider implements NumberProviderInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getNumber(Example $example): int
    {
        return (int) $this->entityManager
            ->createQueryBuilder()
            ->select('count(e)')
            ->from(Example::class, 'e')
            ->join('e.attempt', 'a')
            ->join('a.session', 's')
            ->where('e.id <= :exampleId')
            ->andWhere('s.user = :user')
            ->getQuery()
            ->setParameters([
                'exampleId' => $example->getId(),
                'user' => $example->getAttempt()->getUser(),
            ])
            ->getSingleScalarResult();
    }
}
