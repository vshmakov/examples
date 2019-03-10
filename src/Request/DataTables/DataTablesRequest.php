<?php

namespace App\Request\DataTables;

final class DataTablesRequest
{
    /** @var int */
    private $draw;

    /** @var int */
    private $start;

    /** @var int */
    private $length;

    public function getDraw(): ?int
    {
        return $this->draw;
    }

    public function setDraw(int $draw): void
    {
        $this->draw = $draw;
    }

    public function getStart(): ?int
    {
        return $this->start;
    }

    public function setStart(int $start): void
    {
        $this->start = $start;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(int $length): void
    {
        $this->length = $length;
    }
}
