<?php

namespace App\Security\EventSubscriber;

use App\Attempt\EventSubscriber\RouteTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class RegistrationConfirmedSubscriber implements EventSubscriberInterface
{
    use RouteTrait;

    private const  ROUTE = 'fos_user_registration_confirmed';

    /** @var UrlGeneratorInterface */
    private $uurlGenerator;

    public function __construct(UrlGeneratorInterface $uurlGenerator)
    {
        $this->uurlGenerator = $uurlGenerator;
    }

    public function onKernelRequest(GetResponseEvent $event): void
    {
        if (!$this->isRoute(self::ROUTE, $event)) {
            return;
        }

        $url = $this->uurlGenerator->generate('homepage');
        $event->setResponse(new RedirectResponse($url));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
