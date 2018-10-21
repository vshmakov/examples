<?php

namespace App\Form;

use App\Entity\Settings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Settings1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('addTime')
            ->add('duration')
            ->add('examplesCount')
            ->add('addFMin')
            ->add('addFMax')
            ->add('addSMin')
            ->add('addSMax')
            ->add('addMin')
            ->add('addMax')
            ->add('subFMin')
            ->add('subFMax')
            ->add('subSMin')
            ->add('subSMax')
            ->add('subMin')
            ->add('subMax')
            ->add('multFMin')
            ->add('multFMax')
            ->add('multSMin')
            ->add('multSMax')
            ->add('multMin')
            ->add('multMax')
            ->add('divFMin')
            ->add('divFMax')
            ->add('divSMin')
            ->add('divSMax')
            ->add('divMin')
            ->add('divMax')
            ->add('addPerc')
            ->add('subPerc')
            ->add('multPerc')
            ->add('divPerc')
            ->add('description')
            ->add('isDemanding')
            ->add('attempt')
            ->add('task')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Settings::class,
        ]);
    }
}
