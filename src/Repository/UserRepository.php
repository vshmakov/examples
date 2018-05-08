<?php

namespace App\Repository;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use App\Entity\User;
use App\Entity\Profile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class UserRepository extends ServiceEntityRepository
{
use BaseTrait;
private $ch;

    public function __construct(RegistryInterface $registry, AuthorizationCheckerInterface $ch)
    {
        parent::__construct($registry, User::class);
$this->ch=$ch;
    }

public function getCurrentProfile($u) {
$pR=$this->er(Profile::class);
$p=$u->getProfile() ?? $pR->findOneByAuthor($u) ?? $pR->findOnePublic();
$testDesc="Тестовый профиль";

if (!$p) {
$p=$pR->getNewByCurrentUser()
->setDescription($testDesc)
->setIsPublic(true);

$em=$this->em();
$em->persist($p);
$em->flush();
}

return $this->ch->isGranted("APPOINT", $p) ? $p : $pR->findOneBy(["description"=>$testDesc, "isPublic"=>true]);
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