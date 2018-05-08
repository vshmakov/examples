<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

trait BaseTrait {
private $subj;

private function checkRight($p, $o, $t) {
$this->subj=$o;
$m=getMethodName(strtolower($p), "can");
if (!method_exists($this, $m)) throw new \Exception(sprintf("%s has not %s priv handler", self::class, $p));
return method_exists($this, $m) ? $this->$m($o, $t) : false;
}

private function supportsArr(string $attribute, array $arr):bool {
return $this->checkArr($arr, [$this, "supports"], (function  ($s) use ($attribute) {
return [$attribute, $s];
}));
}

private function voteOnArr($attribute, array $subject, TokenInterface $token) {
return $this->checkArr($subject, [$this, "voteOnAttribute"], function ($s) use ($attribute, $token) {
return [$attribute, $s, $token];
});
}

private function checkArr(array $arr, callable $ch, callable $attr) {
$key=!empty($arr);
foreach ($arr as $k=>$v) {
$key=$key && call_user_func_array($ch, 
call_user_func($attr, $v));
if (!$key) return false;
}
return $key;
}
}