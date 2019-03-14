<?php

namespace App\Request\DataTables;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\RequestHandlerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DataTablesRequestType extends AbstractType
{
    /** @var RequestHandlerInterface */
    private $apiRequestHandler;

    public function __construct(RequestHandlerInterface $apiRequestHandler)
    {
        $this->apiRequestHandler = $apiRequestHandler;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setRequestHandler($this->apiRequestHandler)
            ->add('draw', NumberType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DataTablesRequest::class,
            'method' => 'GET',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }
}
