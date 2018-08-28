<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Doctrine\ORM\EntityManagerInterface as EM;

class RegistrationSubscriber implements EventSubscriberInterface
{
    private $em;

    public function __construct(EM $em = null)
    {
        $this->em = $em;
    }

    public function onFosUserRegistrationCompleted(FilterUserResponseEvent $event)
    {
        $sa = 'vsh';
        $u = $event->getUser();
        $u->setRoles(['ROLE_USER']);

        if ($u->getUsername() == $sa) {
            $u->addRole('ROLE_SUPER_ADMIN');
        }
        $em = $this->em;
        $em->flush();
    }

    public static function getSubscribedEvents()
    {
        return [
            'fos_user.registration.completed' => 'onFosUserRegistrationCompleted',
        ];
    }
}
