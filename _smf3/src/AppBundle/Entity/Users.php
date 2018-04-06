<?php

namespace AppBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;

class Users extends BaseUser
{
    protected $id;
    protected $profile;

public function getId() {
return $this->id;
}

public function getProfile() {
return $this->profile;
}

public function getProfileOrPublic() {
return $this->profile ?? em()->getRepository('AppBundle:Profiles')->findOneByIsPublic(true);
}

public function setProfile($profile) {
$this->profile=$profile;
return $this;
}

}

