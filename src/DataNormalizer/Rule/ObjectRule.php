<?php

namespace App\DataNormalizer\Rule;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class ObjectRule
{
    /** @var string */
    private $checkField;

    /** @var string */
    private $checkExpression;

    /** @var string */
    private $defaultExpression;

    public function __construct(string $checkField, string $checkExpression, string $defaultExpression)
    {
        $this->checkField = $checkField;
        $this->checkExpression = $checkExpression;
        $this->defaultExpression = $defaultExpression;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public function evaluate(array $values)
    {
        $isValid = $this->isValid($values);

        if ($isValid) {
            return $values[$this->checkField];
        }

        return self::getExpressionLanguage()->evaluate($this->defaultExpression, $values);
    }

    public function isValid(array $values): bool
    {
        return self::getExpressionLanguage()->evaluate($this->checkExpression, $values);
    }

    private static function getExpressionLanguage(): ExpressionLanguage
    {
        return new ExpressionLanguage();
    }
}
