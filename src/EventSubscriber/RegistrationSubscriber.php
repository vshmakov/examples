<?php

namespace App\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Entity\User\Role;
final class RegistrationSubscriber implements EventSubscriberInterface
{
    /** @var EntityManagerInterface  */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function onFosUserRegistrationCompleted(FilterUserResponseEvent $event)
    {
        $user = $event->getUser();
        $user->addRole();
                $this->entityManager->flush();
    }

    public static function getSubscribedEvents()
    {
        return [
            'fos_user.registration.completed' => 'onFosUserRegistrationCompleted',
        ];
    }
}
