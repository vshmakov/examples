<?php

namespace App\Task\Contractor;

use App\Entity\Task;

interface ContractorProviderInterface
{
    public function getSolvedTaskContractors(Task $task): array;

    public function getSolvedContractorsCount(Task $task): int;

    public function getNotSolvedTaskContractors(Task $task): array;
}
