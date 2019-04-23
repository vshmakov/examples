<?php

namespace App\Request\Pagination;

use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

final class PaginationRequestValidationSubscriber implements EventSubscriberInterface
{
    /** @var PaginationRequestProviderInterface */
    private $paginationRequestProvider;

    public function __construct(PaginationRequestProviderInterface $paginationRequestProvider)
    {
        $this->paginationRequestProvider = $paginationRequestProvider;
    }

    /**
     * @throws BadRequestHttpException
     */
    public function onKernelRequest(GetResponseEvent $event): void
    {
        if (!$this->paginationRequestProvider->isPaginationRequestValid()) {
            throw new BadRequestHttpException();
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', EventPriorities::PRE_READ],
        ];
    }
}
