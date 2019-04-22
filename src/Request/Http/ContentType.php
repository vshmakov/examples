<?php

declare(strict_types=1);

namespace App\Request\Http;

abstract class ContentType
{
    public const  ACCEPT_HEADER = 'accept';

    public const  JSON = 'application/json';
    public const  JSONDT = 'application/dt+json';
}
