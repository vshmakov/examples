<?php

namespace App\Request;

abstract class ContentType
{
    public const  ACCEPT_HEADER = 'accept';

    public const  JSON = 'application/json';
    public const  JSONDT = 'application/dt+json';
}
