<?php

declare(strict_types=1);

namespace App\Controller\Traits;

use App\Entity\Profile;
use App\Security\Voter\ProfileVoter;

trait ProfileTrait
{
    private function saveAndAppointProfile(Profile $profile): void
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($profile);
        $entityManager->flush($profile);

        if ($this->isGranted(ProfileVoter::APPOINT, $profile)) {
            $currentUser = $this->getCurrentUserOrGuest();
            $currentUser->setProfile($profile);
            $entityManager->flush($currentUser);
        }
    }
}
