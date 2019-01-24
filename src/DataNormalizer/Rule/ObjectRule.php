<?php

namespace App\DataNormalizer\Rule;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class ObjectRule
{
    /**
     * @var object
     */
    private $object;

    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var string
     */
    private $ruleExpression;

    /**
     * @var string
     */
    private $defaultValueExpression;

    public function __construct(object $object, string $fieldName, string $ruleExpression, string $defaultValueExpression)
    {
        $this->object = $object;
        $this->fieldName = $fieldName;
        $this->ruleExpression = $ruleExpression;
        $this->defaultValueExpression = $defaultValueExpression;
    }

    /**
     * @throws \LogicException
     */
    public function normalize(): void
    {
        if (!$this->isValid()) {
            $this->createPropertyAccessor()->setValue(
                $this->object,
                $this->fieldName,
                $this->getDefaultValue()
            );
        }

        if (!$this->isValid()) {
            throw new \LogicException('Default value is not valid');
        }
    }

    /**
     * @return mixed
     */
    private function getDefaultValue()
    {
        return $this->evaluate($this->defaultValueExpression, ['object' => $this->object]);
    }

    /**
     * @throws \LogicException
     */
    public function isValid(): bool
    {
        $isValid = $this->evaluateByObjectField($this->ruleExpression);

        if (!\is_bool($isValid)) {
            throw new \LogicException(
                sprintf('Rule expression "%s" das not return bollean value', $this->ruleExpression)
            );
        }

        return $isValid;
    }

    /**
     * @return mixed
     */
    private function evaluateByObjectField(string $expression)
    {
        return $this->evaluate($expression, [$this->fieldName => $this->getValue()]);
    }

    /**
     * @return mixed
     */
    private function evaluate(string $expression, array $properties)
    {
        $expressionLanguage = new ExpressionLanguage();

        return $expressionLanguage->evaluate($expression, $properties);
    }

    /**
     * @return mixed
     */
    private function getValue()
    {
        return $this->createPropertyAccessor()->getValue($this->object, $this->fieldName);
    }

    private function createPropertyAccessor(): PropertyAccessor
    {
        return PropertyAccess::createPropertyAccessorBuilder()
            ->enableExceptionOnInvalidIndex()
            ->getPropertyAccessor();
    }
}
