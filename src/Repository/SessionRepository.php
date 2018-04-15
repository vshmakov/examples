<?php

namespace App\Repository;

use App\Service\SessionMarker;
use App\Service\UserLoader;
use App\Entity\Session;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\RequestStack as Req;
use Symfony\Component\HttpFoundation\Session\Session as Sess;
use Symfony\Component\HttpFoundation\Session\SessionInterface as SI;

class SessionRepository extends ServiceEntityRepository
{
use BaseTrait;
private $ul;
private $sm;

    public function __construct(RegistryInterface $registry, UserLoader $ul, SessionMarker $sm)
    {
        parent::__construct($registry, Session::class);
$this->ul=$ul;
$this->sm=$sm;
    }

public function findByCurrentUserOrGetNew() {
return $this->findByUserOrGetNew($this->ul->getUser());
}

public function findByUserOrGetNew($u) {
$sid=$this->sm->getKey();
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
