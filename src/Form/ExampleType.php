<?php

namespace App\Form;

use App\Entity\Example;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ExampleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('first')
            ->add('sign')
            ->add('second')
            ->add('answer')
            ->add('isRight')
            ->add('addTime')
            ->add('answerTime')
            ->add('attempt')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Example::class,
        ]);
    }
}
