<?php

namespace App\Request\Ulogin;

use App\Entity\User\SocialAccount;
use App\Request\BaseApiType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UloginRequestType extends BaseApiType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('network', TextType::class)
            ->add('uid', TextType::class, [
                'property_path' => 'networkId',
            ])
            ->add('first_name', TextType::class)
            ->add('last_name', TextType::class)
        ->add('profile', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => SocialAccount::class,
        ]);
    }
}
