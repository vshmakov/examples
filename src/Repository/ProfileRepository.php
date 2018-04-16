<?php

namespace App\Repository;

use App\Entity\Profile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Service\UserLoader;

class ProfileRepository extends ServiceEntityRepository
{
use BaseTrait;
private $ul;

    public function __construct(RegistryInterface $registry, UserLoader $ul)
    {
        parent::__construct($registry, Profile::class);
$this->ul=$ul;
    }

public function findOneByAuthor($u) {
return $this->v($this->q("select p from App:Profile p
where p.author = :u
")->setParameter("u", $u)
);
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

public function findByCurrentAuthor() {
return $this->findByAuthor($this->ul->getUser());
}
}
