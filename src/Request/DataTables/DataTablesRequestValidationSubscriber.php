<?php

namespace App\Request\DataTables;

use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

final class DataTablesRequestValidationSubscriber implements EventSubscriberInterface
{
    /** @var DataTablesRequestProviderInterface */
    private $dataTablesRequestProvider;

    public function __construct(DataTablesRequestProviderInterface $dataTablesRequestProvider)
    {
        $this->dataTablesRequestProvider = $dataTablesRequestProvider;
    }

    /**
     * @throws BadRequestHttpException
     */
    public function onKernelRequest(GetResponseEvent $event): void
    {
        if ($this->dataTablesRequestProvider->isDataTablesRequest() && !$this->dataTablesRequestProvider->isDataTablesRequestValid()) {
            throw new  BadRequestHttpException();
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', EventPriorities::PRE_READ],
        ];
    }
}
