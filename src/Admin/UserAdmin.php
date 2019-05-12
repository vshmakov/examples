<?php

namespace App\Admin;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class UserAdmin extends BaseAdmin
{
    public function __construct($code, $class, $baseControllerName)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->datagridValues = [
                '_sort_by' => 'lastVisitedAt',
            ] + $this->datagridValues;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->add('loginAs', $this->getRouterIdParameter().'/login-as');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('username', TextType::class);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('username')
            ->add('id')
            ->add('firstName')
            ->add('lastName');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id')
            ->addIdentifier('username')
            ->add('firstName')
            ->add('lastName')
            ->add('email')
            ->add('socialAccounts')
            ->add('lastVisitedAt', static::TYPE_DATETIME)
            ->add('attemptsCount')
            ->add('isEnabled', static::TYPE_BOOLEAN)
            ->add('rolesString', null, [
                'label' => 'Roles',
            ])
            ->add('_action', null, [
                'actions' => [
                    'loginAs' => [],
                ],
            ]);
    }
}
