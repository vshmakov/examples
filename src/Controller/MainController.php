<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MainController extends Controller {
protected function em() {
return $this->getDoctrine()->getManager();
}
}