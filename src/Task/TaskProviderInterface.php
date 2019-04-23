<?php

namespace App\Task;

interface TaskProviderInterface
{
    public function getActualTasksOfCurrentUser(): array;

    public function getArchiveTasksOfCurrentUser(): array;
}
