<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Session;
use App\Entity\Visit;
use App\Object\ObjectAccessor;
use App\Repository\IpRepository;
use App\Security\User\CurrentUserProviderInterface;
use App\Security\User\CurrentUserSessionProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class ResponseSubscriber implements EventSubscriberInterface
{
    /** @var Request|null */
    private $request;

    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var SessionInterface */
    private $session;

    /** @var IpRepository */
    private $ipRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var CurrentUserSessionProviderInterface */
    private $currentUserSessionProvider;

    public function __construct(
        CurrentUserSessionProviderInterface $currentUserSessionProvider,
        RequestStack $requestStack,
        CurrentUserProviderInterface $currentUserProvider,
        IpRepository $ipRepository,
        AuthorizationCheckerInterface $authorizationChecker,
        SessionInterface $session,
        EntityManagerInterface $entityManager
    ) {
        $this->session = $session;
        $this->currentUserSessionProvider = $currentUserSessionProvider;
        $this->ipRepository = $ipRepository;
        $this->request = $requestStack->getMasterRequest();
        $this->currentUserProvider = $currentUserProvider;
        $this->authorizationChecker = $authorizationChecker;
        $this->entityManager = $entityManager;
    }

    public function onKernelResponse(FilterResponseEvent $event): void
    {
        $currentUserSession = $this->currentUserSessionProvider->getCurrentUserSession();

        if (!$this->request or null === $currentUserSession) {
            return;
        }

        $clientIp = $this->request->getClientIp();

        if (null === $clientIp) {
            return;
        }

        $this->updateSession($currentUserSession);
        $this->saveVisit($currentUserSession, $event->getResponse()->getStatusCode());
        $this->saveIp($currentUserSession, $clientIp);
    }

    private function updateSession(Session $session): void
    {
        $session->setLastTime(new \DateTime());
        $this->entityManager->flush($session);
    }

    private function saveVisit(Session $session, int $statusCode): void
    {
        $request = $this->request;
        $uri = $request->getRequestUri();
        $routeName = $request->attributes->get('_route', $uri);

        if ('_wdt' !== $routeName &&
            !$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $visit = ObjectAccessor::initialize(Visit::class, [
                'uri' => $uri,
                'routeName' => $routeName,
                'method' => $request->getMethod(),
                'statusCode' => $statusCode,
                'session' => $session,
            ]);

            $this->entityManager->persist($visit);
            $this->entityManager->flush($visit);
        }
    }

    private function saveIp(Session $session, string $clientIp): void
    {
        $user = $this->currentUserProvider->getCurrentUserOrGuest();
        $ip = $this->ipRepository->findOneByIpOrNew($clientIp);

        if (null !== $ip) {
            $session->setIp($ip);
            $this->entityManager->flush($session);

            if (!$this->currentUserProvider->isCurrentUserGuest()) {
                $user->addIp($ip);
                $this->entityManager->flush($user);
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}
