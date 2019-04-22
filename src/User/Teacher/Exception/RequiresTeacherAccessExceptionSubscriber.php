<?php

declare(strict_types=1);

namespace App\User\Teacher\Exception;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequiresTeacherAccessExceptionSubscriber implements EventSubscriberInterface
{
    /** @var EngineInterface */
    private $templating;

    public function __construct(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    final public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        $supportedException = $this->getSupportedException();

        if (!$event->getException() instanceof $supportedException) {
            return;
        }

        $response = $this->templating->renderResponse($this->getTemplate());
        $response->setStatusCode(Response::HTTP_FORBIDDEN);
        $event->setResponse($response);
    }

    protected function getSupportedException(): string
    {
        return RequiresTeacherAccessException::class;
    }

    protected function getTemplate(): string
    {
        return 'exception/requires_teacher_access_exception.html.twig';
    }

    final public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
