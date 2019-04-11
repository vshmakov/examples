<?php

namespace App\Attempt\Examples\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Attempt\EventSubscriber\RouteTrait;
use App\Attempt\Example\ExampleResponseProviderInterface;
use App\Iterator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ShowExamplesCollectionSubscriber implements EventSubscriberInterface
{
    use  RouteTrait;

    public const  ROUTE = 'api_examples_get_collection';

    /** @var ExampleResponseProviderInterface */
    private $exampleResponseProvider;

    public function __construct(ExampleResponseProviderInterface $exampleResponseProvider)
    {
        $this->exampleResponseProvider = $exampleResponseProvider;
    }

    public function onKernelView(GetResponseForControllerResultEvent $event): void
    {
        if (!$this->isRoute(self::ROUTE, $event)) {
            return;
        }

        $event->setControllerResult(
            array_reverse(Iterator::map($event->getControllerResult(), [$this->exampleResponseProvider, 'createExampleResponse']))
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onKernelView', EventPriorities::PRE_SERIALIZE],
        ];
    }
}
