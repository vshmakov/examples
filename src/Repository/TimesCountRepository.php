<?php

namespace App\Repository;

use App\Entity\TimesCount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TimesCount|null find($id, $lockMode = null, $lockVersion = null)
 * @method TimesCount|null findOneBy(array $criteria, array $orderBy = null)
 * @method TimesCount[]    findAll()
 * @method TimesCount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TimesCountRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TimesCount::class);
    }

//    /**
//     * @return TimesCount[] Returns an array of TimesCount objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TimesCount
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
