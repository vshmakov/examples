<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class UserRepository extends ServiceEntityRepository
{
use BaseTrait;

    public function __construct(RegistryInterface $registry, ProfileRepository $pR)
    {
        parent::__construct($registry, User::class);
$this->pR=$pR;
    }

public function getSelfOrPublicProfile($u) {
$p=$u->getProfile() ?? $this->pR->findOneByUser($u) ?? $this->pR->findOnePublic();
if (!$p) throw new \Exception("Принадлежащие данному пользователю и общие профили отсутствуют");
return $p;
}
}
