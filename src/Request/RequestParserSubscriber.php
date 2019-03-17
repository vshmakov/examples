<?php

namespace App\Request;

use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Webmozart\Assert\Assert;

final class RequestParserSubscriber implements EventSubscriberInterface
{
    public function onKernelRequest(GetResponseEvent $event): void
    {
        $request = $event->getRequest();

        if (0 === mb_strpos($request->headers->get('CONTENT_TYPE'), 'application/json')
            && \in_array(mb_strtoupper($request->server->get('REQUEST_METHOD', 'GET')), ['PUT'], true)
        ) {
            $data = json_decode($request->getContent(), true);
            Assert::isArray($data);
            $request->request = new ParameterBag($data);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', EventPriorities::POST_READ],
        ];
    }
}
