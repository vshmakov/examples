<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class LastVisitSubscriber implements EventSubscriberInterface
{
private $sR;

public function __construct(\App\Repository\SessionRepository $sR) {
$this->sR=$sR;
}

    public function onKernelResponse(FilterResponseEvent $event)
    {
if ($s=$this->sR->findOneByCurrentUser()) {
$s->setLastTime(new \DateTime);
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
