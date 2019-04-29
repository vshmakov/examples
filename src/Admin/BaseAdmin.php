<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;

abstract class BaseAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_sort_order' => 'DESC',
    ];
}
