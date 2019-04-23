<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
final class NumberBetween extends Constraint
{
    /** @var int */
    public $minimum;

    /** @var int */
    public $maximum;

    /** @var string */
    public $message = 'The value must be greater than or equal to {{ minimum }} and less than or equal to {{ maximum }}.';
}
