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

public function getAttemptsCount($u) {
return $this->v($this->q("select count(a) from App:User u
join u.sessions s
join s.attempts a
where u = :u")
->setParameter("u", $u));
}

public function getExamplesCount($u) {
return $this->v($this->q("select count(e) from App:User u
join u.sessions s
join s.attempts a
join a.examples e
where u = :u")
->setParameter("u", $u));
}

public function getProfilesCount($u) {
return $this->v($this->q("select count(p) from App:User u
join u.profiles p
where u = :u")
->setParameter("u", $u));
}
}