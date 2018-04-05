<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class TriesRepository extends EntityRepository {

public function isActualTry($try) {
return $try->getSession() == er('s')->getCurrentUserSessionOrNull() &&
$try->getLimitTime()->getTimestamp() >= time() &&
$try->getExamplesCount() > $try->getSolvedExamplesCount();
}

public function getAllCurrentUserTries() {
return createQuery(
'select t from %s t
where t.session in (:s)', ['t']
)->setParameter('s', er('s')->getAllCurrentUserSessions())->getResult();
}

public function getCurrentUserTryByIdOrNull($id) {
return createQuery(
'select t from %s t
where t.id = :id and t.session in (:s)', ['t']
)->setParameters(['id'=>$id, 's'=>er('s')->getAllCurrentUserSessions()])->getOneOrNullResult();
}

public function getCurrentUserLastOrNewTry() {
return $this->getCurrentUserLastTryOrNull() ?? $this->getNewTry();
}

public function getCurrentUserLastTryOrNull() {
$try=createQuery(
"select t from %s t
where t.id in
(select max(t1.id) from %1\$s t1
where t1.session = :s)", ['t']
)->setParameter('s', er('s')->getCurrentUserSessionOrNull())->getOneOrNullResult();
return ($try && $try->isActual()) ? $try : null;
}

public function getNewTry() {
$t=a('t');
$try= new $t;
$this->initialize($try);
em()->persist($try);
em()->flush();
return $try;
}

protected function initialize($try) {
$try->setSettings(er('p')->getCurrentUserOrPublicProfile()->getData());
$try->setSession(er('s')->getCurrentUserOrNewSession());
}

}