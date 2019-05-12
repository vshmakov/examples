<?php

namespace App\Attempt\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Attempt\AttemptResponseFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ShowAttemptSubscriber implements EventSubscriberInterface
{
    use RouteTrait;

    private const  ROUTE = 'api_attempts_get_item';

    /** @var AttemptResponseFactoryInterface */
    private $attemptResponseProvider;

    public function __construct(AttemptResponseFactoryInterface $attemptResponseProvider)
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
}
