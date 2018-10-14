<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
->add('username')
->add('email')
->add('firstName')
->add('fatherName')
->add('lastName')
->add('network')
->add('networkId')
->add('roles')
->add('money')
->add('limitTime')
            ->add('profile')
->add('isSocial')
->add('enabled')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'validation_groups' => false,
        ]);
    }
}
