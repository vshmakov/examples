<?php

declare(strict_types=1);

namespace App\Task\Homework;

interface HomeworkProviderInterface
{
    public function getActualHomework(): array;

    public function getArchiveHomework(): array;
}
