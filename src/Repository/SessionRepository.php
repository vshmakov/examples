<?php

namespace App\Repository;

use App\Entity\Session;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\RequestStack as Req;
use Symfony\Component\HttpFoundation\Session\Session as Sess;

class SessionRepository extends ServiceEntityRepository
{
use BaseTrait;
private $r;

    public function __construct(RegistryInterface $registry, Req $r
)
    {
        parent::__construct($registry, Session::class);
$this->r=$r->getCurrentRequest();
    }

public function findByUserOrGetNew($u) {
$r=$this->r;
if (!$r->hasPreviousSession()) {
$sess=(new Sess());
$sess->start();
$r->setSession($sess);
}
$sid=$r->getSession()->getId();
if ($s=$this->findOneBySid($sid)) return $s;

$s=(new Session())
->setUser($u)
->setSid($sid);
$em=$this->em();
$em->persist($s);
$em->flush();
return $s;
}
}
