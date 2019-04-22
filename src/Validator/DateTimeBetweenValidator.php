<?php

declare(strict_types=1);

namespace App\Validator;

use App\DateTime\DateInterval as DTI;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use  Webmozart\Assert\Assert;

final class DateTimeBetweenValidator extends ConstraintValidator
{
    /**
     * @param \DateTimeInterface       $value
     * @param DateTimeBetweenValidator $constraint
     *
     * @throws \InvalidArgumentException
     */
    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($value, \DateTimeInterface::class);
        Assert::isInstanceOf($constraint, DateTimeBetween::class);
        Assert::notEmpty($constraint->minimum);
        Assert::notEmpty($constraint->maximum);

        $minimumInterval = DTI::createFromDateIntervalString($constraint->minimum);
        $maximumInterval = DTI::createFromDateIntervalString($constraint->maximum);

        if ($value->getTimestamp() < $minimumInterval->getTimestamp() or $value->getTimestamp() > $maximumInterval->getTimestamp()) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ minimum }}', $minimumInterval->format('%I:%S'))
                ->setParameter('{{ maximum }}', $maximumInterval->format('%I:%S'))
                ->addViolation();
        }
    }
}
