<?php

namespace App\Attempt\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Attempt\AttemptResponseProviderInterface;
use App\Iterator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ShowAttemptsCollectionSubscriber implements EventSubscriberInterface
{
    use  RouteTrait;

    public const  ROUTE = 'api_attempts_get_collection';

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

        $event->setControllerResult(
            array_reverse(Iterator::map($event->getControllerResult(), [$this->attemptResponseProvider, 'createAttemptResponse']))
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onKernelView', EventPriorities::PRE_SERIALIZE],
        ];
    }
}
