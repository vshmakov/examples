<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use FOS\UserBundle\Event\FormEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResettingSubscriber implements EventSubscriberInterface
{
    private $ug;

    public function __construct(UrlGeneratorInterface $ug)
    {
        $this->ug = $ug;
    }

    public function onFosUserResettingResetSuccess(FormEvent $event)
    {
        $url = $this->ug->generate('homepage');
        $event->setResponse(new RedirectResponse($url));
    }

    public static function getSubscribedEvents()
    {
        return [
            'fos_user.resetting.reset.success' => 'onFosUserResettingResetSuccess',
        ];
    }
}
