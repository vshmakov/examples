<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RequiresTeacherAccessException extends AccessDeniedHttpException
{
}
