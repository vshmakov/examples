<?php

declare(strict_types=1);

namespace App\Attempt\Example\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\ApiPlatform\Filter\SupportsTaskFilteringInterface;
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
    private $exampleResponseFactory;

    /** @var SupportsTaskFilteringInterface */
    private $supportsTaskFiltering;

    /** @var NumberProviderInterface */
    private $userNumberProvider;

    /** @var NumberProviderInterface */
    private $taskNumberProvider;

    public function __construct(
        ExampleResponseFactoryInterface $exampleResponseFactory,
        SupportsTaskFilteringInterface $supportsTaskFiltering,
        NumberProviderInterface $userNumberProvider,
        NumberProviderInterface  $taskNumberProvider
    ) {
        $this->exampleResponseFactory = $exampleResponseFactory;
        $this->supportsTaskFiltering = $supportsTaskFiltering;
        $this->userNumberProvider = $userNumberProvider;
        $this->taskNumberProvider = $taskNumberProvider;
    }

    public function onKernelView(GetResponseForControllerResultEvent $event): void
    {
        if (!$this->isRoute(self::ROUTE, $event)) {
            return;
        }

        $numberProvider = $this->userNumberProvider;

        if ($this->supportsTaskFiltering->isTaskFiltering($event)) {
            $numberProvider = $this->taskNumberProvider;
        }

        $event->setControllerResult(
            Iterator::map($event->getControllerResult(), function (Example $example) use ($numberProvider): ExampleResponse {
                return $this->exampleResponseFactory->createExampleResponse($example, $numberProvider);
            })
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onKernelView', EventPriorities::PRE_SERIALIZE],
        ];
    }
}
