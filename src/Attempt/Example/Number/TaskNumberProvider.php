<?php

declare(strict_types=1);

namespace App\Attempt\Example\Number;

use App\Entity\Example;
use Doctrine\ORM\EntityManagerInterface;
use Webmozart\Assert\Assert;

final class TaskNumberProvider implements NumberProviderInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getNumber(Example $example): int
    {
        Assert::notNull($example->getAttempt()->getTask());

        return (int) $this->entityManager
            ->createQueryBuilder()
            ->select('count(e)')
            ->from(Example::class, 'e')
            ->join('e.attempt', 'a')
            ->where('e.id <= :exampleId')
            ->andWhere('a.task = :task')
            ->getQuery()
            ->setParameters([
                'exampleId' => $example->getId(),
                'task' => $example->getAttempt()->getTask(),
            ])
            ->getSingleScalarResult();
    }
}
