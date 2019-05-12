<?php

namespace App\Request\Ip;

use App\Entity\Ip;
use App\Request\BaseApiType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class IpInfoRequestType extends BaseApiType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('continentName', TextType::class, [
                'property_path' => 'continent',
            ])
            ->add('countryName', TextType::class, [
                'property_path' => 'country',
            ])
            ->add('stateProv', TextType::class, [
                'property_path' => 'region',
            ])
            ->add('city', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => Ip::class,
        ]);
    }
}
