<?php

namespace App\Admin;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class UserAdmin extends BaseAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('username', TextType::class);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('username')
            ->add('id');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id')
            ->addIdentifier('username')
            ->add('email')
            ->add('lastVisitedAt')
            ->add('attemptsCount')
        ->add('isEnabled', static::TYPE_BOOLEAN);
    }
}
