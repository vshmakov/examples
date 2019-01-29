<?php

namespace App\Security\Annotation;

use Sensio\Bundle\FrameworkExtraBundle\Request\ArgumentNameConverter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class IsGrantedListener implements EventSubscriberInterface
{
    /** @var ArgumentNameConverter */
    private $argumentNameConverter;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(ArgumentNameConverter $argumentNameConverter, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->argumentNameConverter = $argumentNameConverter;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function onKernelControllerArguments(FilterControllerArgumentsEvent $event)
    {
        $request = $event->getRequest();

        /** @var $configurations IsGranted[] */
        if (!$configurations = $request->attributes->get('_'.IsGranted::ALIAS)) {
            return;
        }

        $arguments = $this->argumentNameConverter->getControllerArguments($event);

        foreach ($configurations as $configuration) {
            $subject = null;
            if ($configuration->getSubject()) {
                if (!isset($arguments[$configuration->getSubject()])) {
                    throw new \RuntimeException(sprintf('Could not find the subject "%s" for the @IsGranted annotation. Try adding a "$%s" argument to your controller method.', $configuration->getSubject(), $configuration->getSubject()));
                }

                $subject = $arguments[$configuration->getSubject()];
            }

            if (!$this->authorizationChecker->isGranted($configuration->getAttributes(), $subject)) {
                $argsString = $this->getIsGrantedString($configuration);

                $message = $configuration->getMessage() ?: sprintf('Access Denied by controller annotation @IsGranted(%s)', $argsString);
                $exception = new AccessDeniedException($message);

                if ($exceptionClass = $configuration->GetException()) {
                    $exception = new $exceptionClass();
                }

                if ($statusCode = $configuration->getStatusCode()) {
                    $exception = new HttpException($statusCode, $message);
                }

                throw $exception;
            }
        }
    }

    private function getIsGrantedString(IsGranted $isGranted)
    {
        $attributes = array_map(function ($attribute) {
            return sprintf('"%s"', $attribute);
        }, (array) $isGranted->getAttributes());
        if (1 === \count($attributes)) {
            $argsString = reset($attributes);
        } else {
            $argsString = sprintf('[%s]', implode(', ', $attributes));
        }

        if (null !== $isGranted->getSubject()) {
            $argsString = sprintf('%s, %s', $argsString, $isGranted->getSubject());
        }

        return $argsString;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [KernelEvents::CONTROLLER_ARGUMENTS => 'onKernelControllerArguments'];
    }
}
