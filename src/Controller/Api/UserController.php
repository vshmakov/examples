<?php

namespace App\Controller\Api;

use App\Attempt\AttemptResponseProviderInterface;
use App\Iterator;

final class UserController
{
    public function attempts(iterable $data, AttemptResponseProviderInterface $attemptResponseProvider): iterable
    {
        return Iterator::map($data, [$attemptResponseProvider, 'createAttemptResponse']);
    }
}
