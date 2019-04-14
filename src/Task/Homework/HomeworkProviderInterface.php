<?php

namespace App\Task\Homework;

interface HomeworkProviderInterface
{
    public function getActualHomeworkOfCurrentUserTeacher(): array;
}
