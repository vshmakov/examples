<?php

declare(strict_types=1);

namespace App\Request\DataTables;

interface DataTablesRequestProviderInterface
{
    public function getDataTablesRequest(): ?DataTablesRequest;

    public function isDataTablesRequest(): bool;

    public function isDataTablesRequestValid(): ?bool;
}
