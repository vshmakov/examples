<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class NumberBetweenValidator extends ConstraintValidator
{
    /**
     * @param int           $value
     * @param NumberBetween $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if ($value < $constraint->minimum or $value > $constraint->maximum) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ minimum }}', $constraint->minimum)
                ->setParameter('{{ maximum }}', $constraint->maximum)
                ->addViolation();
        }
    }
}
