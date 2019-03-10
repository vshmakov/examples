<?php

namespace App\Request\DataTables;

interface DataTablesRequestProviderInterface
{
    public function getDataTablesRequest(): ?DataTablesRequest;

    public function hasDataTablesRequest(): bool;
}
