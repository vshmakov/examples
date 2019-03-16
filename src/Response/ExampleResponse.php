<?php

namespace App\Response;

use App\Entity\Example;
use App\Serializer\Group;
use  Symfony\Component\Serializer\Annotation\Groups;

final class ExampleResponse
{
    /**
     * @var Example
     */
    private $example;

    /**
     * @var int
     * @Groups({Group::ATTEMPT})
     */
    private $number;

    /** @var \DateTimeInterface|null */
    private $solvingTime;

    public function __construct(
        int $number,
        ?\DateTimeInterface $solvingTime,
        Example $example)
    {
        $this->number = $number;
        $this->solvingTime = $solvingTime;
        $this->example = $example;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @Groups({Group::ATTEMPT})
     */
    public function getString(): string
    {
        return $this->example->__toString();
    }

    public function getSolvingTime(): ?\DateTimeInterface
    {
        return $this->solvingTime;
    }

    public function getExample(): Example
    {
        return $this->example;
    }
}
