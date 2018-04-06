<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MainController extends Controller {
protected function canOrAcc($p, $o) {
return $this->denyAccessUnlessGranted($p, $o);
}
}