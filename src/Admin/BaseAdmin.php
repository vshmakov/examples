<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;

abstract class BaseAdmin extends AbstractAdmin
{
    protected const  TYPE_BOOLEAN = 'boolean';
    protected const  TYPE_DATETIME = 'datetime';

    protected $datagridValues = [
        '_sort_order' => 'DESC',
    ];
}
