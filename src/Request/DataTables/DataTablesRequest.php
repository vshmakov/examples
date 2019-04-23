<?php

namespace App\Request\DataTables;

use Symfony\Component\Validator\Constraints as Assert;

final class DataTablesRequest
{
    /**
     * @var int
     * @Assert\NotNull
     */
    private $draw;

    public function getDraw(): ?int
    {
        return $this->draw;
    }

    public function setDraw(?int $draw): void
    {
        $this->draw = $draw;
    }
}
