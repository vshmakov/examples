<?php

namespace App\EventSubscriber;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Service\UserLoader;
use App\Repository\SessionRepository;
use App\Repository\IpRepository;
use App\Entity\Visit;
use App\Service\AuthChecker as CH;

class ResponseSubscriber implements EventSubscriberInterface
{
    private $sR;
    private $ipR;
    private $req;
    private $ul;
    private $ch;
    private $session;

    public function __construct(SessionRepository $sR, RequestStack $rs, UserLoader $ul, IpRepository $ipR, CH $ch, SessionInterface $session)
    {
        $this->session = $session;
        $this->sR = $sR;
        $this->ipR = $ipR;
        $this->req = $rs->getMasterRequest();
        $this->ul = $ul;
        $this->ch = $ch;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $req = $this->req;
        $em = $this->sR->em();
        $s = $this->sR->findOneByCurrentUser();

        if ($req && $event->isMasterRequest() && $s && !$this->session->getFlashBag()->get('missResponseEvent', [])) {
            $uri = $req->getRequestUri();
            $rn = $req->attributes->get('_route', $uri);

            if ('_wdt' != $rn && !$this->ch->isGranted('ROLE_ADMIN')) {
                $v = (new Visit())
->setUri($uri)
->setRouteName($rn)
->setMethod($req->getMethod())
->setSession($s)
->setStatusCode($event->getResponse()->getStatusCode());
                $em->persist($v);
            }

            $s->setLastTime(new \DateTime());

            $u = $this->ul->getUser();
            $ip = $this->ipR->findOneByIpOrNew($req->getClientIp());

            if ($ip) {
                if (!$this->ul->isGuest()) {
                    $u->addIp($ip);
                }
                $s->setIp($ip);
            }

            $em->flush();
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.response' => 'onKernelResponse',
        ];
    }
}
