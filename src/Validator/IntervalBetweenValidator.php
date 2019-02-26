<?php

namespace App\Validator;

use App\DateTime\DateInterval as DTI;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use  Webmozart\Assert\Assert;

final class IntervalBetweenValidator extends ConstraintValidator
{
    /**
     * @param \DateInterval
     * @param IntervalBetweenValidator $constraint
     *
     * @throws \InvalidArgumentException
     */
    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($value, \DateInterval::class);
        Assert::isInstanceOf($constraint, IntervalBetween::class);
        Assert::notEmpty($constraint->minimum);
        Assert::notEmpty($constraint->maximum);

        $minimumInterval = DTI::createFromDateIntervalString($constraint->minimum);
        $maximumInterval = DTI::createFromDateIntervalString($constraint->maximum);

        if (!DTI::createFromDateInterval($value)->isBetween($minimumInterval, $maximumInterval)) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ minimum }}', $minimumInterval->format('%I:%S'))
                ->setParameter('{{ maximum }}', $maximumInterval->format('%I:%S'))
                ->addViolation();
        }
    }
}
