<?php

namespace App\ApiPlatform;

abstract class Attribute
{
    public const  FORMAT = '_format';
    public const  RESOURCE_CLASS = '_api_resource_class';
    public const  COLLECTION_OPERATION_NAME = '_api_collection_operation_name';
    public const  PAGINATION = '_api_pagination';
    public const  PAGE = 'page';
    public const  ITEMS_PER_PAGE = 'items_per_page';
}
