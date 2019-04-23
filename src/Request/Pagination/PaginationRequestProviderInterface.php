<?php

namespace App\Request\Pagination;

interface PaginationRequestProviderInterface
{
    public function getPaginationRequest(): ?PaginationRequest;

    public function isPaginationRequestValid(): bool;
}
