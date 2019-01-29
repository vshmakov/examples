<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\User\Role;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
                /** @var User $user */
                $user = $event->getData();

                if ($user->isTeacher()) {
                    $user->removeRole(Role::STUDENT);
                    $user->addRole(Role::TEACHER);
                } else {
                    $user->removeRole(User\Role::TEACHER);
                    $user->addRole(User\Role::STUDENT);
                }
            })
            ->add('save', SubmitType::class, ['label' => 'Сохранить']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'validation_groups' => ['Default', 'account'],
        ]);
    }
}
