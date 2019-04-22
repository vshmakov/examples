<?php

declare(strict_types=1);

namespace App\Task;

interface TaskProviderInterface
{
    public function getActualTasksOfCurrentUser(): array;

    public function getArchiveTasksOfCurrentUser(): array;
}
