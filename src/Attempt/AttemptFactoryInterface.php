<?php

namespace App\Attempt;

use App\Entity\Attempt;
use App\Entity\Task;

interface AttemptFactoryInterface
{
    public function createAttempt(): Attempt;

    public function createTaskAttempt(Task $task): Attempt;
}
