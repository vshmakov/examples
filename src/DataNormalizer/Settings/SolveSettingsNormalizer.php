<?php

namespace App\DataNormalizer\Settings;

use App\DataNormalizer\Rule\ObjectRule;
use App\Entity\BaseProfile as Settings;
use App\Object\ObjectAccessor;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SolveSettingsNormalizer implements SettingsNormalizerInterface
{
    private const CURRENT_FIELD_NAME = 'currentField';
    private const  PREVIOUS_FIELD_NAME = 'previousField';

    /** @var NormalizerInterface */
    private $normalizer;

    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public function normalize(Settings $settings): void
    {
        $this->normalizePercent($settings);
        $this->normalizeArithmetic($settings);
    }

    private function normalizePercent(Settings $settings): void
    {
    }

    private function normalizeArithmetic(Settings $settings): void
    {
        $addFMin = 'addFMin';
        $addFMax = 'addFMax';
        $addSMin = 'addSMin';
        $addSMax = 'addSMax';
        $addMin = 'addMin';
        $addMax = 'addMax';
        $currentField = self::CURRENT_FIELD_NAME;
        $previousField = self::PREVIOUS_FIELD_NAME;
        $rule = function (string $checkExpression, string $defaultExpression) use ($currentField): ObjectRule {
            return new   ObjectRule($currentField, $checkExpression, $defaultExpression);
        };
        $graterThanPreviousFieldRule = $rule("$currentField > $previousField", $previousField);
        $lessThan = function (string $checkExpression) use ($currentField, $rule): ObjectRule {
            return $rule("$currentField < $checkExpression", $checkExpression);
        };
        $graterThan = function (string $checkExpression) use ($currentField, $rule): ObjectRule {
            return $rule("$currentField > $checkExpression", $checkExpression);
        };
        $rules = [
            $addMin => [$graterThan("$addFMin + $addSMin"), $lessThan("$addFMax + $addSMax")],
            $addMax => [$lessThan("$addFMax + $addSMax"), $graterThanPreviousFieldRule],
        ];

        $this->setBasicFieldsRules($rules, $graterThanPreviousFieldRule);
        $this->applyRules($settings, $rules, function ($value, string $field, bool $isValid) use ($settings): void {
            ObjectAccessor::setValue($settings, $field, $value);
        });
    }

    private function setBasicFieldsRules(array &$rules, ObjectRule $graterThanPreviousFieldRule): void
    {
        $basicRules = [];

        foreach (['add', 'sub', 'mult', 'div'] as $arithmeticFunctionName) {
            foreach (['f', 's'] as $number) {
                $createFieldName = function (string $limit) use ($arithmeticFunctionName, $number): string {
                    return Inflector::camelize("{$arithmeticFunctionName}_{$number}_{$limit}");
                };

                $basicRules += [$createFieldName('min') => [], $createFieldName('max') => [$graterThanPreviousFieldRule]];
            }
        }

        $rules = $basicRules + $rules;
    }

    private function applyRules(Settings $settings, array $rules, callable $applyCallback): void
    {
        foreach ($rules as $field => $fieldRules) {
            /** @var ObjectRule $rule */
            foreach ($fieldRules as $rule) {
                $settingsData = $this->normalizer->normalize($settings);
                $previousFieldValue = isset($previousField) ? $settingsData[$previousField] : null;
                $values = $settingsData + [
                        self::CURRENT_FIELD_NAME => $settingsData[$field],
                        self::PREVIOUS_FIELD_NAME => $previousFieldValue,
                    ];
                $applyCallback($rule->evaluate($values), $field, $rule->isValid($values));
            }

            $previousField = $field;
        }
    }
}
