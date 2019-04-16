<?php

namespace App\Task\Contractor;

use App\Entity\Task;
use App\Entity\User;
use App\Response\Result\ContractorResult;

interface ContractorResultFactoryInterface
{
    public function createContractorResult(User $contractor, Task $task): ContractorResult;
}
