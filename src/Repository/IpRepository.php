<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Ip;
use App\Object\ObjectAccessor;
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

    public function hasOrCreateByIp($ip): ?Ip
    {
        if (!IpInformer::isIp($ip)) {
            return null;
        }

        $entity = $this->findOneByIp($ip);

        if (!$entity) {
            $entity = ObjectAccessor::initialize(Ip::class, ['ip' => $ip]);
            $entityManager = $this->getEntityManager();
            $entityManager->persist($entity);
            $entityManager->flush($entity);
        }

        return $entity;
    }

    public function findOneByIpOrNew($ip)
    {
        return $this->hasOrCreateByIp($ip);
    }
}
