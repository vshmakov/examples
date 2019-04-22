<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Ip;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IpType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ip')
            ->add('country')
            ->add('region')
            ->add('city')
            ->add('addTime')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ip::class,
        ]);
    }
}
