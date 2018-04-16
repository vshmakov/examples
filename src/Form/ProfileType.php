<?php

namespace App\Form;

use App\Entity\Profile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('addTime')
            ->add('isPublic')
            ->add('minutes')
            ->add('seconds')
            ->add('examplesCount')
            ->add('addMin')
            ->add('addMax')
            ->add('subMin')
            ->add('subMax')
            ->add('minSub')
            ->add('multMin')
            ->add('multMax')
            ->add('divMin')
            ->add('divMax')
            ->add('minDiv')
            ->add('addPerc')
            ->add('subPerc')
            ->add('multPerc')
            ->add('divPerc')
            ->add('author')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Profile::class,
        ]);
    }
}
