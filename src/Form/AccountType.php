<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
->add('username', null, ['label' => 'Логин'])
->add('firstName', null, ['label' => 'Имя'])
->add('fatherName', null, ['label' => 'Отчество'])
->add('lastName', null, ['label' => 'Фамилия'])
->add('isTeacher', CheckboxType::class, ['label' => 'Учитель', 'required' => false])
->add('save', SubmitType::class, ['label' => 'Сохранить'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'validation_groups' => ['Default', 'account'],
        ]);
    }
}
