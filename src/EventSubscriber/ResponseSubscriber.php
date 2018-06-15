<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\{
Session\SessionInterface,
RequestStack,
};
use App\Service\UserLoader;
use App\Repository\SessionRepository;

class ResponseSubscriber implements EventSubscriberInterface
{
private $sR;
private $req;
private $ul;

public function __construct(SessionRepository $sR, RequestStack $rs, UserLoader $ul) {
$this->sR=$sR;
$this->req=$rs->getMasterRequest();
$this->ul=$ul;
}

    public function onKernelResponse(FilterResponseEvent $event)
    {
if ($s=$this->sR->findOneByCurrentUser()) {
$s->setLastTime(new \DateTime);

if ($req=$this->req) {
$u=$this->ul->getUser();
$u->addIp($req->getClientIp());
}

$this->sR->em()->flush();
}
    }

    public static function getSubscribedEvents()
    {
        return [
           'kernel.response' => 'onKernelResponse',
        ];
    }
}
