<?php

namespace AppBundle\Service;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RightsChecker {
private $authChecker;

public function __construct(AuthorizationCheckerInterface $authChecker) {
$this->authChecker=$authChecker;
}

public function canCreateProfiles() {
return $this->isUser();}

public function canChooseProfiles() {
return $this->canCreateProfiles();
}

public function canDeleteProfiles() {
return $this->canCreateProfiles();
}

public function canEditProfiles() {
return $this->canCreateProfiles();
}

public function canAppointPublicProfiles() {
return $this->isAdmin();
}

public function canDeletePublicProfiles() {
return $this->canAppointPublicProfiles();
}

public function isUser() {
return $this->authChecker->isGranted('ROLE_USER');
}

public function isGuest() {
return !$this->isUser();
}

public function isAdmin() {
return $this->authChecker->isGranted('ROLE_ADMIN');
}

}