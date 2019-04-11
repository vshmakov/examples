<?php

namespace App\Request\DataTables;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

final class BadRequestHttpExceptionSubscriber implements EventSubscriberInterface
{
    /** @var DataTablesRequestProviderInterface */
    private $dataTablesRequestProvider;

    public function __construct(DataTablesRequestProviderInterface $dataTablesRequestProvider)
    {
        $this->dataTablesRequestProvider = $dataTablesRequestProvider;
    }

    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        if ($this->dataTablesRequestProvider->isDataTablesRequest() && $event->getException() instanceof BadRequestHttpException) {
            $event->setResponse(new JsonResponse(['error' => $event->getException()->getMessage()], Response::HTTP_BAD_REQUEST));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
