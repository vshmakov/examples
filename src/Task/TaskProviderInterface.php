<?php

namespace App\Task;

interface TaskProviderInterface
{
    public function getActualTasksOfCurrentTeacher(): array;
}
