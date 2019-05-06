<?php

namespace App\EventSubscriber;

use App\Entity\Session;
use App\Entity\Visit;
use App\Object\ObjectAccessor;
use App\Security\User\CurrentUserProviderInterface;
use App\Security\User\CurrentUserSessionProviderInterface;
use App\User\Visit\Ip\IpProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class LogSubscriber implements EventSubscriberInterface
{
    /** @var Request|null */
    private $request;

    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var SessionInterface */
    private $session;

    /** @var IpProviderInterface */
    private $ipProvider;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var CurrentUserSessionProviderInterface */
    private $currentUserSessionProvider;

    public function __construct(
        CurrentUserSessionProviderInterface $currentUserSessionProvider,
        RequestStack $requestStack,
        CurrentUserProviderInterface $currentUserProvider,
        IpProviderInterface $ipProvider,
        AuthorizationCheckerInterface $authorizationChecker,
        SessionInterface $session,
        EntityManagerInterface $entityManager
    ) {
        $this->session = $session;
        $this->currentUserSessionProvider = $currentUserSessionProvider;
        $this->ipProvider = $ipProvider;
        $this->request = $requestStack->getMasterRequest();
        $this->currentUserProvider = $currentUserProvider;
        $this->authorizationChecker = $authorizationChecker;
        $this->entityManager = $entityManager;
    }

    public function saveLogs(KernelEvent $event): void
    {
        $isGetRequest = $event->getRequest()->isMethod(Request::METHOD_GET);

        if ($isGetRequest) {
            $this->updateCurrentUser();
        }

        $currentUserSession = $this->currentUserSessionProvider->getCurrentUserSession();

        if (!$this->request or null === $currentUserSession) {
            return;
        }

        $this->updateSession($currentUserSession);
        $this->saveVisit($currentUserSession, $event->getResponse()->getStatusCode());

        if ($isGetRequest) {
            $this->saveIp($currentUserSession);
        }
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

        if ('_wdt' === $routeName or $this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            return;
        }

        $visit = ObjectAccessor::initialize(Visit::class, ['uri' => $uri,
            'routeName' => $routeName,
            'method' => $request->getMethod(),
            'statusCode' => $statusCode,
            'session' => $session, ]);

        $this->entityManager->persist($visit);
        $this->entityManager->flush($visit);
    }

    private function saveIp(Session $session): void
    {
        $user = $this->currentUserProvider->getCurrentUserOrGuest();
        $ip = $this->ipProvider->getCurrentRequestIp();

        if (null === $ip) {
            return;
        }

        $session->setIp($ip);
        $this->entityManager->flush($session);

        if (!$this->currentUserProvider->isCurrentUserGuest()) {
            $user->addIp($ip);
            $this->entityManager->flush($user);
        }
    }

    private function updateCurrentUser(): void
    {
        $currentUser = $this->currentUserProvider->getCurrentUserOrGuest();
        $currentUser->setLastVisitedAt(new \DateTime());
        $this->entityManager->flush($currentUser);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'saveLogs',
        ];
    }
}
