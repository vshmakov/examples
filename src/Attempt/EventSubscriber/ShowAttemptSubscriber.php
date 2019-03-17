<?php

namespace App\Attempt\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\ApiPlatform\Attribute;
use App\Attempt\AttemptResponseProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ShowAttemptSubscriber implements EventSubscriberInterface
{
    private const  ROUTE = 'api_attempts_get_item';

    /** @var AttemptResponseProviderInterface */
    private $attemptResponseProvider;

    public function __construct(AttemptResponseProviderInterface $attemptResponseProvider)
    {
        $this->attemptResponseProvider = $attemptResponseProvider;
    }

    public function onKernelView(GetResponseForControllerResultEvent $event): void
    {
        if (!$this->isRoute(self::ROUTE, $event)) {
            return;
        }

        $attempt = $event->getControllerResult();

        $event->setControllerResult(
            $this->attemptResponseProvider->createAttemptResponse($attempt)
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onKernelView', EventPriorities::PRE_SERIALIZE],
        ];
    }

    private function isRoute(string $route, KernelEvent $event): bool
    {
        return $route === $event->getRequest()->attributes->get(Attribute::ROUTE);
    }
}
