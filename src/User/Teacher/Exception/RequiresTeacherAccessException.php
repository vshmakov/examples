<?php

declare(strict_types=1);

namespace App\User\Teacher\Exception;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class RequiresTeacherAccessException extends AccessDeniedHttpException
{
}
