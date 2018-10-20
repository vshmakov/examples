<?php

namespace App\Repository;

use App\Entity\Settings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Service\UserLoader;

class ProfileRepository extends ServiceEntityRepository
{
    use BaseTrait;
    private $userLoader;

    public function __construct(RegistryInterface $registry, UserLoader $userLoader)
    {
        parent::__construct($registry, Profile::class);
        $this->userLoader = $userLoader;
    }
}
