<?php

namespace App\EventSubscriber;

use App\Entity\Session;
use App\Entity\Visit;
use App\Repository\IpRepository;
use App\Security\User\CurrentUserSessionProviderInterface;
use App\Service\UserLoader;
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

    /** @var UserLoader */
    private $userLoader;

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
        UserLoader $userLoader,
        IpRepository $ipRepository,
        AuthorizationCheckerInterface $authorizationChecker,
        SessionInterface $session,
        EntityManagerInterface $entityManager
    ) {
        $this->session = $session;
        $this->currentUserSessionProvider = $currentUserSessionProvider;
        $this->ipRepository = $ipRepository;
        $this->request = $requestStack->getMasterRequest();
        $this->userLoader = $userLoader;
        $this->authorizationChecker = $authorizationChecker;
        $this->entityManager = $entityManager;
    }

    public function onKernelResponse(FilterResponseEvent $event): void
    {
        $currentUserSession = $this->currentUserSessionProvider->getCurrentUserSession();
        $missResponseEvent = $this->session->getFlashBag()->get('missResponseEvent', []);

        if (!$this->request or $this->request->isMethod('POST') or $missResponseEvent or null === $currentUserSession) {
            return;
        }

        $clientIp = $this->request->getClientIp();

        if (null === $clientIp) {
            return;
        }

        $this->updateSession($currentUserSession);
        $this->saveVisit($currentUserSession, $event->getResponse()->getStatusCode());
        $this->saveIp($currentUserSession, $clientIp);
        $this->entityManager->flush();
    }

    private function updateSession(Session $session): void
    {
        $session->setLastTime(new \DateTime());
    }

    private function saveVisit(Session $session, int $statusCode): void
    {
        $request = $this->request;
        $uri = $request->getRequestUri();
        $routeName = $request->attributes->get('_route', $uri);

        if ('_wdt' !== $routeName &&
            !$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $visit = (new Visit())
                ->setUri($uri)
                ->setRouteName($routeName)
                ->setMethod($request->getMethod())
                ->setSession($session)
                ->setStatusCode($statusCode);

            $this->entityManager->persist($visit);
        }
    }

    private function saveIp(Session $session, string $clientIp): void
    {
        $user = $this->userLoader->getUser();
        $ip = $this->ipRepository->findOneByIpOrNew($clientIp);

        if ($ip) {
            $session->setIp($ip);

            if (!$this->userLoader->isCurrentUserGuest()) {
                $user->addIp($ip);
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}
