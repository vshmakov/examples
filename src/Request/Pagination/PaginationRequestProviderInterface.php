<?php

declare(strict_types=1);

namespace App\Request\Pagination;

interface PaginationRequestProviderInterface
{
    public function getPaginationRequest(): ?PaginationRequest;

    public function isPaginationRequestValid(): bool;
}
