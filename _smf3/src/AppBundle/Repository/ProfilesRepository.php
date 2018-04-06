<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ProfilesRepository extends EntityRepository {

public function isCurrentUserProfile($profile) {
return $profile === er('u')->getCurrentUserOrGuest()->getProfile();
}

public function getCurrentUserProfileOrNull() {
return er('u')->getCurrentUserOrGuest()->getProfile();
}

public function getAllCurrentUserProfiles() {
return $this->findByUser(er('u')->getCurrentUserOrGuest());
}

public function getCurrentUserProfileByIdOrNull($id) {
return createQuery(
'select p from %s p
where p.id = :id and p.user = :user', ['p']
)->setParameters(['id'=>$id, 'user'=>er('u')->getCurrentUserOrGuest()])->getOneOrNullResult();
}

public function getCurrentUserOrPublicProfile() {
return er('u')->getCurrentUserOrGuest()->getProfile() ?? $this->getPublicProfile();
}

public function GetPublicProfile() {
return createQuery(
'select p from %s p
where p.isPublic = true', ['p']
)->setMaxResults(1)->getOneOrNullResult();
}

public function getCurrentUserOrPublicProfileByIdOrNull($id) {
return createQuery(
'select p from %s p
where p.id = :id and (p.user = :user or p.isPublic = true)', ['p']
)->setParameters(['id'=>$id, 'user'=>er('u')->getCurrentUserOrGuest()])->getOneOrNullResult();
}

public function initialize($profile) {
$profile->setUser(er('u')->getCurrentUserOrGuest());
$profile->setDescription($this->getDefaultDescription());
return $profile;
}

protected function getDefaultDescription() {
return "Профиль №".(createQuery(
'select count(p) from %s p
where p.user = :u', ['p']
)->setParameter('u', er('u')->getCurrentUserOrGuest())->getOneOrNullResult()[1]+1);
}

}