<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use \Symfony\Component\HttpFoundation\Session\Session;

class SessionsRepository extends EntityRepository {

public function getAllCurrentUserSessions() {
return $this->findByUser(er('u')->getCurrentUserOrGuest());
}

public function getNewSession() {
$s=a('s');
$session=new $s;
$this->initialize($session);
em()->persist($session);
em()->flush();
return $session;
}

public function getCurrentUserOrNewSession() {
return $this->getCurrentUserSessionOrNull() ?? $this->getNewSession();
}

public function getCurrentUserSessionOrNull() {
$rs=new Session();
$sId=$rs->getId();
$s=null;
if ($sId && $s=er('s')->findOneBySid($sId)) $this->initialize($s);
return $s;
}

protected function initialize($session) {
$session->setRealSession(new Session());
$session->start();
$session->setSid($session->getRealSession()->getId());
$session->setUser(er('u')->getCurrentUserOrGuest());
$session->setLastVisitTime(new \DateTime());
}

}