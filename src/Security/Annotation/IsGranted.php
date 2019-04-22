<?php

declare(strict_types=1);

namespace App\Security\Annotation;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted as BaseSecurity;

/**
 * @Annotation
 */
final class IsGranted extends BaseSecurity
{
    public const ALIAS = 'my.is_granted';

    /** @var string|null */
    private $exception;

    public function getAliasName(): string
    {
        return self::ALIAS;
    }

    /**
     * @return string|null
     */
    public function getException(): ?string
    {
        return $this->exception;
    }

    public function setException(string $exception): void
    {
        $this->exception = $exception;
    }
}
