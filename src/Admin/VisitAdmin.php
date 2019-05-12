<?php

namespace App\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;

final class VisitAdmin extends BaseAdmin
{
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id')
            ->add('session.user.username', null, [
                'label' => 'Username',
            ])
            ->add('routeName')
            ->add('statusCode')
            ->add('uri')
            ->add('method')
            ->add('addTime');
    }
}
