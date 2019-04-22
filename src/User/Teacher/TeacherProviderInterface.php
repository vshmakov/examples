<?php

declare(strict_types=1);

namespace App\User\Teacher;

interface TeacherProviderInterface
{
    public function getTeachers(): array;
}
