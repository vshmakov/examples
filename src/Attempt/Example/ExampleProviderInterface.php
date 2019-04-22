<?php

declare(strict_types=1);

namespace App\Attempt\Example;

use App\Entity\Task;
use App\Entity\User;

interface ExampleProviderInterface
{
    public function getRightExamplesCount(User $contractor, Task $task): int;
}
