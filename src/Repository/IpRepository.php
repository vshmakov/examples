<?php

namespace App\Repository;

use App\Entity\Ip;
use App\Repository\Traits\BaseTrait;
use App\Service\IpInformer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class IpRepository extends ServiceEntityRepository
{
    use BaseTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Ip::class);
    }

    public function hasOrCreateByIp($ip)
    {
        if (!IpInformer::isIp($ip)) {
            return false;
        }

        $entity = $this->findOneByIp($ip);

        if (!$entity) {
            $entity = (new Ip())->setIp($ip);
            $entityManager = $this->getEntityManager();
            $entityManager->persist($entity);
            $entityManager->flush();
        }

        return $entity;
    }

    public function findOneByIpOrNew($ip)
    {
        return $this->hasOrCreateByIp($ip);
    }
}
