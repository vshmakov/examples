<?php

namespace App\Controller\Api;

use App\Iterator;
use App\Response\AttemptResponseProviderInterface;

final class UserController
{
    public function attempts(iterable $data, AttemptResponseProviderInterface $attemptResponseProvider): iterable
    {
        return Iterator::map($data, [$attemptResponseProvider, 'createAttemptResponse']);
    }
}
