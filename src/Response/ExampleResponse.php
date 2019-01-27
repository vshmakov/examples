<?php

namespace App\Response;

use App\Entity\Example;
use Symfony\Component\Serializer\Annotation\Groups;

final class ExampleResponse
{
    /**
     * @var Example
     */
    private $example;

    /**
     * @var int
     * @Groups({"attempt"})
     */
    private $number;

    public function __construct(Example $example, int $number)
    {
        $this->example = $example;
        $this->number = $number;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @Groups({"attempt"})
     */
    public function getString(): string
    {
        return $this->example->__toString();
    }
}
