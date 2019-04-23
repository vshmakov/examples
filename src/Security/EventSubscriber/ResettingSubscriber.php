<?php

namespace App\Security\EventSubscriber;

use FOS\UserBundle\Event\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ResettingSubscriber implements EventSubscriberInterface
{
    /** @var UrlGeneratorInterface */
    private $uurlGenerator;

    public function __construct(UrlGeneratorInterface $uurlGenerator)
    {
        $this->uurlGenerator = $uurlGenerator;
    }

    public function onFosUserResettingResetSuccess(FormEvent $event): void
    {
        $url = $this->uurlGenerator->generate('homepage');
        $event->setResponse(new RedirectResponse($url));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'fos_user.resetting.reset.success' => 'onFosUserResettingResetSuccess',
        ];
    }
}
