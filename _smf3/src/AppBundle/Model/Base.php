<?php

namespace AppBundle\Model;

class Base {

public function __get($var) {
return call_user_func([$this, 'get'.ucfirst($var)]);
}

public function __call($method, $params=[]) {
$divName=$this->processMethodName($method);

if (in_array(@$divName->action, ['get', 'set'])) {
$property=$divName->property;
return ($divName->action=='get') ? $this->$property : $this->$property=$params[0];
} 
}

private function processMethodName($method) {
foreach (['get', 'set'] as $action) {
if (preg_match("#^$action(.+)$#", $method, $arr)) {
return (object) ['action'=>$action, 'property'=>lcfirst($arr[1])];
}
}

return (object) ['action'=>'get', 'property'=>$method];
}

}