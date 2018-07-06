<?php

namespace App\Repository;

use App\Entity\Ip;
use App\Service\IpInformer as IpInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class IpRepository extends ServiceEntityRepository
{
use BaseTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Ip::class);
    }

public function hasOrCreateByIp($ip) {
if (IpInfo::isIp($ip) && !$this->findOneByIp($ip)) {
$e=(new Ip)->setIp($ip);
$em=$this->em();
$em->persist($e);
$em->flush();
}
}

public function findOneByIpOrNew($ip) {
if ($e=$this->findOneByIp($ip)) return $e;
if (!IpInfo::isIp($ip)) return  false;

$e=(new Ip)->setIp($ip);
$em=$this->em();
$em->persist($e);
$em->flush();
return $e;
}
}
