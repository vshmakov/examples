<?php

namespace App\User\Teacher\Exception;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class RequiresTeacherAccessException extends AccessDeniedHttpException
{
}
