<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\User\Role;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class)
            ->add('firstName', TextType::class)
            ->add('fatherName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('isTeacher', CheckboxType::class, ['required' => false])
            ->add('save', SubmitType::class)
            ->addEventListener(FormEvents::POST_SUBMIT, [$this, 'userRoleSubscriber']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'validation_groups' => ['Default', 'account'],
        ]);
    }

    public function userRoleSubscriber(FormEvent $event): void
    {
        /** @var User $user */
        $user = $event->getData();

        if ($user->isTeacher()) {
            $user->addRole(Role::TEACHER);
        } else {
            $user->addRole(Role::STUDENT);
        }
    }
}
