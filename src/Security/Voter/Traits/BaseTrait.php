<?php

namespace App\Security\Voter\Traits;

use App\Entity\User;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

trait BaseTrait
{
    protected $subject;

    private function supportsUser($attribute, $subject)
    {
        return (($subject instanceof User) or null === $subject) && $this->hasHandler($attribute);
    }

    private function voteOnNamedCallback($attribute, $subject, TokenInterface $token): bool
    {
        $this->subject = $subject;

        $handlerName = $this->getHandlerName($attribute);

        return $this->$handlerName();
    }

    private function hasHandler($attribute)
    {
        return method_exists($this, $this->getHandlerName($attribute));
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

        return Inflector::camelize(mb_strtolower($prefix.'_'.$attribute));
    }
}
