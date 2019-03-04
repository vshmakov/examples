<?php

namespace App\Form;

use App\Entity\Profile;
use App\Object\ObjectAccessor;
use App\Serializer\Group;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

class ProfileType extends AbstractType
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

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description')
            ->add('durationInterval', DateIntervalType::class, [
                'widget' => 'text',
                'with_years' => false,
                'with_months' => false,
                'with_days' => false,
                'with_minutes' => true,
                'with_seconds' => true,
            ])
            ->add('examplesCount')
            ->add('addPerc')
            ->add('subPerc')
            ->add('multPerc')
            ->add('divPerc')
            ->add('isDemanding', CheckboxType::class, ['required' => false])
            ->add('save', SubmitType::class)
            ->addEventListener(FormEvents::POST_SUBMIT, [$this, 'normalizePercentData'])
            ->addEventListener(FormEvents::POST_SUBMIT, [$this, 'normalizeSolveSettings']);

        foreach (['add', 'sub', 'mult', 'div'] as $k) {
            foreach (['F', 'S', ''] as $n) {
                foreach (['Min', 'Max'] as $m) {
                    $v = $k.$n.$m;
                    $builder->add($v);
                }
            }
        }
    }

    public function normalizeSolveSettings(FormEvent $event): void
    {
        $settingsData = $this->normalizer->normalize($event->getData(), null, ['group' => Group::SETTINGS]);
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

        $graterThan = function (string $checkExpression) use ($currentField, $rule): callable {
            return $rule("$currentField > $checkExpression", $checkExpression);
        };

        $graterThanPreviousFieldRule = $rule("$currentField > $previousField", $previousField);

        $rules = [
            'addMin' => [$graterThan('addFMin + addSMin'), $lessThan('addFMax + addSMax')],
            'addMin' => [$lessThan('addFMax + addSMax'), $graterThanPreviousFieldRule],
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

        ObjectAccessor::setValues($event->getData(), $normalizedSettings);
    }

    public function normalizePercentData(FormEvent $event): void
    {
        $profile = $event->getData();
        $percentData = ObjectAccessor::getValues($profile, ['addPerc', 'subPerc', 'multPerc', 'divPerc']);

        ObjectAccessor::setValues(
            $profile,
            $this->normalizePercentList($percentData)
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Profile::class,
        ]);
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
