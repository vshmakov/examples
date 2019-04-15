<?php

namespace App\Attempt\Example\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Attempt\EventSubscriber\RouteTrait;
use App\Attempt\Example\ExampleResponseFactoryInterface;
use App\Attempt\Example\Number\NumberProviderInterface;
use App\Entity\Example;
use App\Iterator;
use App\Response\ExampleResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ShowExamplesCollectionSubscriber implements EventSubscriberInterface
{
    use  RouteTrait;

    public const  ROUTE = 'api_examples_get_collection';

    /** @var ExampleResponseFactoryInterface */
    private $exampleResponseProvider;

    /** @var NumberProviderInterface */
    private $userNumberProvider;

    public function __construct(ExampleResponseFactoryInterface $exampleResponseProvider, NumberProviderInterface $userNumberProvider)
    {
        $this->exampleResponseProvider = $exampleResponseProvider;
        $this->userNumberProvider = $userNumberProvider;
    }

    public function onKernelView(GetResponseForControllerResultEvent $event): void
    {
        if (!$this->isRoute(self::ROUTE, $event)) {
            return;
        }

        $event->setControllerResult(
            array_reverse(Iterator::map($event->getControllerResult(), function (Example $example): ExampleResponse {
                return $this->exampleResponseProvider->createExampleResponse($example, $this->userNumberProvider);
            }))
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onKernelView', EventPriorities::PRE_SERIALIZE],
        ];
    }
}
