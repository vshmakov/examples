<?php

declare(strict_types=1);

namespace App\Request\Pagination;

use App\Validator as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;

final class PaginationRequest
{
    /**
     * @var int
     * @Assert\NotNull
     * @Assert\GreaterThanOrEqual(0)
     */
    private $start = 0;

    /**
     * @var int
     * @Assert\NotNull
     * @AppAssert\NumberBetween(minimum=1, maximum=500)
     */
    private $length = 30;

    public function getStart(): ?int
    {
        return $this->start;
    }

    public function setStart(?int $start): void
    {
        $this->start = $start;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(?int $length): void
    {
        $this->length = $length;
    }
}
