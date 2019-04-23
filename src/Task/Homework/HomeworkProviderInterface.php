<?php

namespace App\Task\Homework;

interface HomeworkProviderInterface
{
    public function getActualHomework(): array;

    public function getArchiveHomework(): array;
}
