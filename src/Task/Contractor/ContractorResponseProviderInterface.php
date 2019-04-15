<?php

namespace App\Task\Contractor;

use App\Entity\Task;
use App\Entity\User;
use App\Response\ContractorResponse;

interface ContractorResponseProviderInterface
{
    public function createContractorResponse(User $contractor, Task $task): ContractorResponse;
}
