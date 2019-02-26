<?php

namespace App\Form;

use App\Entity\Profile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProfileType extends AbstractType
{
    private $ch;

    public function __construct(AuthorizationCheckerInterface $ch)
    {
        $this->ch = $ch;
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
                'with_minutes' => false,
                'with_seconds' => true,
            ])
            ->add('examplesCount')
            ->add('addPerc')
            ->add('subPerc')
            ->add('multPerc')
            ->add('divPerc')
            ->add('isDemanding', CheckboxType::class, ['required' => false])
            ->add('submit', SubmitType::class);

        foreach (['add', 'sub', 'mult', 'div'] as $k) {
            foreach (['F', 'S', ''] as $n) {
                foreach (['Min', 'Max'] as $m) {
                    $v = $k.$n.$m;
                    $builder->add($v);
                }
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Profile::class,
        ]);
    }
}
