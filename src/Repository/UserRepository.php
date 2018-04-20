<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Profile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class UserRepository extends ServiceEntityRepository
{
use BaseTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

public function getCurrentProfile($u) {
$pR=$this->er(Profile::class);
$p=$u->getProfile() ?? $pR->findOneByAuthor($u) ?? $pR->findOnePublic();
if (!$p) throw new \Exception("Принадлежащие данному пользователю и общие профили отсутствуют");
return $p;
}
}
