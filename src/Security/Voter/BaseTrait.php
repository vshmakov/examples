<?php

namespace App\Security\Voter;

use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

trait BaseTrait
{
    private $subject;

    protected function checkRight($attribute, $subject, TokenInterface $token)
    {
        $this->subject = $subject;
        $handlerName = $this->getHandlerName($attribute);

        if (!method_exists($this, $handlerName)) {
            throw new \Exception(sprintf('%s has not %s priv handler, attempted to find %s method', self::class, $attribute, $handlerName));
        }

        return $this->$handlerName();
    }

    private function hasPrefix($prefix, $attribute)
    {
        return preg_match("#^{$prefix}_#", $attribute);
    }

    private function getHandlerName($attribute)
    {
        $prefix = 'can';

        if ($this->hasPrefix('IS', $attribute)) {
            $prefix = '';
        }

        if ($this->hasPrefix('PRIV', $attribute)) {
            $prefix = 'has';
        }

        return Inflector::camelize($prefix.'_'.$attribute);
    }

    private function hasHandler($attribute)
    {
        return method_exists($this, $this->getHandlerName($attribute));
    }

    private function supportsArr(string $attribute, array $subjects) : bool
    {
        return $this->checkArr($subjects, [$this, 'supports'], (function ($supports) use ($attribute) {
            return [$attribute, $supports];
        }));
    }

    private function voteOnArr($attribute, array $subjects, TokenInterface $token)
    {
        return $this->checkArr($subjects, [$this, 'voteOnAttribute'], function ($subjects) use ($attribute, $token) {
            return [$attribute, $subjects, $token];
        });
    }

    private function checkArr(array $arr, callable $checker, callable $attr)
    {
        $key = !empty($arr);

        foreach ($arr as $k => $v) {
            $key = $key && call_user_func_array(
                $checker,
                call_user_func($attr, $v)
            );

            if (!$key) {
                return false;
            }
        }

        return $key;
    }
}
