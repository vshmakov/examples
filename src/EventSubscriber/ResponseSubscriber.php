<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\{
Session\SessionInterface,
RequestStack,
};
use App\Service\UserLoader;
use App\Repository\{SessionRepository, IpRepository};
use App\Entity\Ip;

class ResponseSubscriber implements EventSubscriberInterface
{
private $sR;
private $ipR;
private $req;
private $ul;

public function __construct(SessionRepository $sR, RequestStack $rs, UserLoader $ul, IpRepository $ipR) {
$this->sR=$sR;
$this->ipR=$ipR;
$this->req=$rs->getMasterRequest();
$this->ul=$ul;
}

    public function onKernelResponse(FilterResponseEvent $event)
    {
if ($s=$this->sR->findOneByCurrentUser()) {
$s->setLastTime(new \DateTime);
$em=$this->sR->em();

if ($req=$this->req) {
$u=$this->ul->getUser();
$ip=(new Ip)->setIp($req->getClientIp());
if ($ip->isValid() && !$this->ipR->countByIp($ip->getIp())) $em->persist($ip);
$u->addIp($ip);
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
