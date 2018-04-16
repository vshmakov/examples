<?php

namespace App\Repository;

use App\Entity\Profile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ProfileRepository extends ServiceEntityRepository
{
use BaseTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Profile::class);
    }

public function findOneByUser($u) {
return $this->v($this->q("select p from App:Profile p
join p.users u
where u = :u
")->setParameter("u", $u)
);
}

public function findOnePublic() {
return $this->v($this->q("select p from App:Profile p
where p.isPublic = true
")
);
}
}
