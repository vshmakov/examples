<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class RequiresStudentAccessException extends AccessDeniedHttpException
{
}
