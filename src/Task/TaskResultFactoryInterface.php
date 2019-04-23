<?php

namespace App\Task;

use App\Entity\Task;
use App\Response\Result\TaskResult;

interface TaskResultFactoryInterface
{
    public function createTaskResult(Task $task): TaskResult;
}
