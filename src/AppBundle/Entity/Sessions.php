<?php

namespace AppBundle\Entity;

use Symfony\Component\HttpFoundation\Session\Session;

class Sessions extends \AppBundle\Model\Base
{
protected $realSession;
    protected $lastVisitTime;
    protected $id;
protected $sid;
    protected $user;

public 	function start() {
if (!$this->opened()) $this->getRealSession()->start();
	}

public function opened() {
return !!$this->getRealSession()->getId();
}

}