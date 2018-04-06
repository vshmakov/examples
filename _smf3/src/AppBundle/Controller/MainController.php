<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response; 
use AppBundle\Service\RightsChecker;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MainController extends Controller {
public static $instance;
protected $rightsChecker;

public function __construct(RightsChecker $rightsChecker) {
self::$instance=$this;
$this->rightsChecker=$rightsChecker;
}

public function __call($method, $params=[]) {
return call_user_func_array([$this, $method], $params);
}

protected function render($view, array $parameters=[], ?Response $response = NULL) {
return parent::render("$view", $parameters+[
'rightsChecker'=>$this->rightsChecker,
], $response);
}

public function __get($var) {
return $this->$var;
}

}