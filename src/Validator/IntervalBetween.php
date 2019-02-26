<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
final class IntervalBetween extends Constraint
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
