<?php

namespace App\Controller\Api;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Api\PaginatorToArrayTrait;
use App\Response\AttemptResponseProviderInterface;

final class UserController
{
    use  PaginatorToArrayTrait;

    public function attempts(PaginatorInterface $data, AttemptResponseProviderInterface $attemptResponseProvider): array
    {
        return array_map([$attemptResponseProvider, 'createAttemptResponse'], $this->paginatorToArray($data));
    }
}
