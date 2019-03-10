<?php

namespace App\Request\DataTables;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DataTablesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('draw')
            ->add('start')
            ->add('length');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DataTablesRequest::class,
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }
}
