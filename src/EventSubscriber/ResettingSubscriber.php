<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use FOS\UserBundle\Event\FormEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResettingSubscriber implements EventSubscriberInterface
{
    private $uurlGenerator;

    public function __construct(UrlGeneratorInterface $uurlGenerator)
    {
        $this->uurlGenerator = $uurlGenerator;
    }

    public function onFosUserResettingResetSuccess(FormEvent $event)
    {
        $url = $this->uurlGenerator->generate('homepage');
        $event->setResponse(new RedirectResponse($url));
    }

    public static function getSubscribedEvents()
    {
        return [
            'fos_user.resetting.reset.success' => 'onFosUserResettingResetSuccess',
        ];
    }
}
