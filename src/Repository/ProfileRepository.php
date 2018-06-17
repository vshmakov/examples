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

public function getTitle($p) {
return $p->getDescription() ?: "Профиль №".$this->getNumber($p);
}

public function countByCurrentAuthor() {
return $this->count(["author"=>$this->ul->getUser()]);
}

public function getNumber($p) {
return ($p->getId()) ? $this->v($this->q("select count(p) from App:Profile p
where p.author =:a and p.id <= :id")
->setParameters(["a"=>$p->getAuthor(), "id"=>$p->getId()])
) : $this->countByCurrentAuthor()+1;
}

public function getNewByCurrentUser() {
$p=(new Profile());
return $p->SetDescription($this->getTitle($p))
->setAuthor($this->ul->getUser());
}

public function getCopyingDescriptionByCurrentUser($p) {
$i=0;

while (true) {
$d=$p->getDescription();
if ($i) $d.=" ($i)";
$c=$this->v($this->q("select count(p) from App:Profile p
where p.description = :d and p.author = :a")
->setParameters(["d"=>$d, "a"=>$this->ul->getUser()])
);
if (!$c) break;
$i++;
}

return ($d);
}
}