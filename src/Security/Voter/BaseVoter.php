<?php

namespace App\Security\Voter;

use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Webmozart\Assert\Assert;

abstract class BaseVoter extends Voter
{
    protected $subject;

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $this->assertInSupportedAttributes($attribute);

        return $this->voteOnNamedCallback($attribute, $subject);
    }

    final protected function inSupportedAttributes(string $attribute): bool
    {
        return \in_array($attribute, static::getSupportedAttributes(), true);
    }

    private function assertInSupportedAttributes(string $attribute): void
    {
        $supportedAttributes = static::getSupportedAttributes();
        Assert::oneOf($attribute, $supportedAttributes, sprintf('%s called with %s attribute, witch does not exist. Available attributes are: %s', static::class, $attribute, implode(', ', $supportedAttributes)));
    }

    abstract protected static function getSupportedAttributes(): array;

    private function voteOnNamedCallback($attribute, $subject): bool
    {
        $this->subject = $subject;

        $handlerName = $this->getHandlerName($attribute);

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

        return Inflector::camelize(mb_strtolower($prefix.'_'.$attribute));
    }
}
