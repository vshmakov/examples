<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class UsersRepository extends EntityRepository {

public function getCurrentUserOrGuest() {
$u=getInstance()->getUser();
$class=a('u');
return ($u instanceof $class) ? $u : $this->findOneByUsername('__guest');
}

}