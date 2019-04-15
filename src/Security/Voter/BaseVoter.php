<?php

namespace App\Security\Voter;

use App\Security\Voter\Traits\BaseTrait;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Webmozart\Assert\Assert;

abstract class BaseVoter extends Voter
{
    use BaseTrait;

    protected $subject;

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $this->assertInSupportedAttributes($attribute);

        return $this->voteOnNamedCallback($attribute, $subject, $token);
    }

    protected function inSupportedAttributes(string $attribute): bool
    {
        return \in_array($attribute, static::getSupportedAttributes(), true);
    }

    private function assertInSupportedAttributes(string $attribute): void
    {
        $supportedAttributes = static::getSupportedAttributes();
        Assert::oneOf($attribute, $supportedAttributes, sprintf('%s called with %s attribute, witch does not exist. Available attributes are: %s', static::class, $attribute, implode(', ', $supportedAttributes)));
    }

    abstract protected static function getSupportedAttributes(): array;
}
