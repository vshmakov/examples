<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Code;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('string')
            ->add('money')
            ->add('addTime')
            ->add('activateTime')
            ->add('activated')
            ->add('user')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Code::class,
        ]);
    }
}
