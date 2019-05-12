<?php

namespace App\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;

final class IpAdmin extends BaseAdmin
{
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id')
            ->add('users')
            ->add('country')
            ->add('region')
            ->add('city')
            ->add('continent')
            ->add('addTime')
            ->add('ip');
    }
}
