<?php

declare(strict_types=1);

namespace App\Entity\User;

final class Role
{
    public const GUEST = 'ROLE_GUEST';
    public const USER = 'ROLE_USER';
    public const STUDENT = 'ROLE_STUDENT';
    public const TEACHER = 'ROLE_TEACHER';
    public const ADMIN = 'ROLE_ADMIN';
    public const SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
}
