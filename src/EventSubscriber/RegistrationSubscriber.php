<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Doctrine\ORM\EntityManagerInterface;

class RegistrationSubscriber implements EventSubscriberInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function onFosUserRegistrationCompleted(FilterUserResponseEvent $event)
    {
        $superAdminUsername = 'vsh';
        $user = $event->getUser();
        $user->setRoles([]);

        if ($user->getUsername() == $superAdminUsername) {
            $user->addRole('ROLE_SUPER_ADMIN');
        }
        
        $this->entityManager->flush();
    }

    public static function getSubscribedEvents()
    {
        return [
            'fos_user.registration.completed' => 'onFosUserRegistrationCompleted',
        ];
    }
}
