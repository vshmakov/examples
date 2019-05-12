<?php

namespace App\User\Student\Exception;

use App\User\Teacher\Exception\RequiresTeacherAccessExceptionSubscriber;

final class RequiresStudentAccessExceptionSubscriber extends RequiresTeacherAccessExceptionSubscriber
{
    protected function getSupportedException(): string
    {
        return RequiresStudentAccessException::class;
    }

    /**
     * @return string
     */
    protected function getTemplate(): string
    {
        return 'exception/requires_student_access_exception.html.twig';
    }
}
