<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
final class DateTimeBetween extends Constraint
{
    /**
     * @var string
     */
    public $minimum;

    /**
     * @var string
     */
    public $maximum;

    /**
     * @var string
     */
    public $message = 'Interval must be greater than {{ minimum }} and less than {{ maximum }}.';
}
