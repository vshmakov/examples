<?php

namespace App\Form;

use App\Attempt\Profile\ProfileNormalizerInterface;
use App\Entity\Profile;
use App\Object\ObjectAccessor;
use App\Serializer\Group;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use  Webmozart\Assert\Assert;

final class ProfileType extends AbstractType implements ProfileNormalizerInterface
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var NormalizerInterface */
    private $normalizer;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker, NormalizerInterface $normalizer)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->normalizer = $normalizer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', TextType::class)
            ->add('duration', MinuteSecondTimeType::class, [
                'widget' => 'text',
                'invalid_message' => 'This value should not be blank.',
            ])
            ->add('examplesCount', NumberType::class)
            ->add('isDemanding', CheckboxType::class, ['required' => false])
            ->add('save', SubmitType::class)
            ->addEventListener(FormEvents::POST_SUBMIT, [$this, 'normalizerListener']);

        $settings = [
            'addFMin', 'addFMax', 'addSMin', 'addSMax', 'addMin', 'addMax',
            'subFMin', 'subFMax', 'subSMin', 'subSMax', 'subMin', 'subMax',
            'multFMin', 'multFMax', 'multSMin', 'multSMax', 'multMin', 'multMax',
            'divFMin', 'divFMax', 'divSMin', 'divSMax', 'divMin', 'divMax',
        ];

        foreach ($settings as $percentField) {
            $builder->add($percentField, NumberType::class);
        }

        foreach (['addPerc', 'subPerc', 'multPerc', 'divPerc'] as $percentField) {
            $builder->add($percentField, PercentType::class, [
                'type' => 'integer',
            ]);
        }
    }

    public function normalizerListener(FormEvent $event): void
    {
        $this->normalize($event->getData());
    }

    public function normalize(Profile $profile): void
    {
        $this->normalizeSolveSettings($profile);
        $this->normalizePercentData($profile);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Profile::class,
        ]);
    }

    private function normalizeSolveSettings(Profile $profile): void
    {
        $settingsData = $this->normalizer->normalize($profile, null, ['groups' => Group::SETTINGS]);
        $currentField = 'currentField';
        $previousField = 'previousField';

        $rule = function (string $checkExpression, string $defaultExpression) use ($currentField): callable {
            return function (array $settingsData) use ($currentField, $checkExpression, $defaultExpression) {
                return $this->evaluateRule($currentField, $checkExpression, $defaultExpression, $settingsData);
            };
        };

        $lessThan = function (string $checkExpression) use ($currentField, $rule): callable {
            return $rule("$currentField < $checkExpression", $checkExpression);
        };

        $greaterThan = function (string $checkExpression) use ($currentField, $rule): callable {
            return $rule("$currentField > $checkExpression", $checkExpression);
        };

        $greaterThanPreviousField = $rule("$currentField > $previousField", $previousField);

        $rules = [
            'addMin' => [$greaterThan('addFMin + addSMin'), $additionUpperLimit = $lessThan('addFMax + addSMax')],
            'addMax' => [$additionUpperLimit, $greaterThanPreviousField],
            'subMin' => [$greaterThan('subFMin - subSMax'), $subtractionUpperLimit = $lessThan('subFMax - subSMin')],
            'subMax' => [$subtractionUpperLimit, $greaterThanPreviousField],
            'multMin' => [$greaterThan('multFMin * multSMin'), $multiplicationUpperLimit = $lessThan('multFMax * multSMax')],
            'multMax' => [$multiplicationUpperLimit, $greaterThanPreviousField],
            'divMin' => [$greaterThan('divFMin / divSMax'), $divisionUpperLimit = $lessThan('divFMax / divSMin')],
            'divMax' => [$divisionUpperLimit, $greaterThanPreviousField],
        ];

        $normalizedSettings = [];
        foreach ($rules as $field => $fieldRules) {
            foreach ($fieldRules as $rule) {
                $previousFieldValue = isset($previousFieldName) ? $settingsData[$previousFieldName] : null;
                $values = $settingsData + [
                        $currentField => $settingsData[$field],
                        $previousField => $previousFieldValue,
                    ];

                $normalizedSettings[$field] = $settingsData[$field] = $rule($values);
            }

            $previousFieldName = $field;
        }

        ObjectAccessor::setValues($profile, $normalizedSettings);
    }

    private function normalizePercentData(Profile $profile): void
    {
        $percentData = ObjectAccessor::getValues($profile, ['addPerc', 'subPerc', 'multPerc', 'divPerc']);

        ObjectAccessor::setValues(
            $profile,
            $this->normalizePercentList($percentData)
        );
    }

    private function normalizePercentList(array $percentList): array
    {
        $totalPercentSum = 0;

        foreach ($percentList as $percent) {
            $totalPercentSum += abs($percent);
        }

        if (0 === $totalPercentSum) {
            $totalPercentSum = 1;
        }

        $percentSum = 0;

        foreach ($percentList as $key => $percent) {
            $percentSum += $percentList[$key] = round($percent / $totalPercentSum * 100);
        }

        foreach (array_reverse($percentList) as $key => $percent) {
            if (0 !== $percent) {
                $percentList[$key] += 100 - $percentSum;

                return $percentList;
            }
        }

        $percentList[$key] += 100 - $percentSum;

        return $percentList;
    }

    /**
     * @return mixed
     */
    private function evaluateRule(string $field, string $checkExpression, string $defaultExpression, array $values)
    {
        $expressionLanguage = new  ExpressionLanguage();
        $isValid = $expressionLanguage->evaluate($checkExpression, $values);
        Assert::boolean($isValid, 'Check expression must return boolean value');

        if ($isValid) {
            return $values[$field];
        }

        return $expressionLanguage->evaluate($defaultExpression, $values);
    }
}
