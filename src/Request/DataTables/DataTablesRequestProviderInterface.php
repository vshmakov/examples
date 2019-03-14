<?php

namespace App\Request\DataTables;

interface DataTablesRequestProviderInterface
{
    public function getDataTablesRequest(): ?DataTablesRequest;

    public function isDataTablesRequest(): bool;

    public function isDataTablesRequestValid(): ?bool;
}
