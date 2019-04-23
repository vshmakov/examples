<?php

namespace App\User\Student\Exception;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class RequiresStudentAccessException extends AccessDeniedHttpException
{
}
