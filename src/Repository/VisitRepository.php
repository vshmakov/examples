<?php

namespace App\Repository;

use App\Entity\Visit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class VisitRepository extends ServiceEntityRepository
{
    use BaseTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Visit::class);
    }

    public function cleareVisits(\DateTimeInterface $dt)
    {
        $entityManager = $this->getEntityManager();
        $visits = $this->createQuery('select v from App:Visit v
where v.addTime < :dt')
            ->setParameter('dt', $dt)
            ->getResult();

        foreach ($visits as $visit) {
            $entityManager->remove($visit);
        }
        $entityManager->flush();

        return \count($visits);
    }
}
