<?php

namespace App\Request;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\RequestHandlerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class BaseApiType extends AbstractType
{
    /** @var RequestHandlerInterface */
    private $apiRequestHandler;

    public function __construct(RequestHandlerInterface $apiRequestHandler = null)
    {
        $this->apiRequestHandler = $apiRequestHandler;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setRequestHandler($this->apiRequestHandler);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }
}
