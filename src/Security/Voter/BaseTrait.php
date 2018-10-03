<?php

namespace App\Security\Voter;

use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

trait BaseTrait
{
    protected $subject;

    protected function checkRight($attribute, $subject, TokenInterface $token)
    {
        $this->subject = $subject;
        $handlerName = $this->getHandlerName($attribute);

        if (!method_exists($this, $handlerName)) {
            throw new \Exception(sprintf('%s has not %s priv handler, attempted to find %s method', self::class, $attribute, $handlerName));
        }

        return $this->$handlerName();
    }

    protected function hasHandler($attribute)
    {
        return method_exists($this, $this->getHandlerName($attribute));
    }

    protected function supportsArr(string $attribute, array $subjects): bool
    {
        return array_reduce(
            $subjects,
            function ($supports, $subject) use ($attribute) {
                if (!$supports) {
                    return false;
                }

                return $this->supports($attribute, $subject);
            },
            !empty($subjects)
        );
    }

    protected function voteOnArr($attribute, array $subjects, TokenInterface $token)
    {
        return array_reduce(
            $subjects,
            function ($vote, $subject) use ($attribute, $token) {
                if (!$vote) {
                    return false;
                }

                return $this->voteOnAttribute($attribute, $subject, $token);
            },
            !empty($subjects)
        );
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
}
