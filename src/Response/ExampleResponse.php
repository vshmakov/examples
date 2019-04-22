<?php

declare(strict_types=1);

namespace App\Response;

use App\Entity\Attempt;
use App\Entity\Example;
use App\Serializer\Group;
use  Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

final class ExampleResponse
{
    /**
     * @var Example
     */
    private $example;

    /**
     * @var int
     * @Groups({Group::ATTEMPT, Group::EXAMPLE})
     */
    private $number;

    /**
     * @var \DateTimeInterface|null
     * @Groups({Group::EXAMPLE})
     */
    private $solvingTime;

    /** @var int|null */
    private $errorNumber;

    /** @var callable */
    private $createAttemptResponse;

    public function __construct(
        int $number,
        ?\DateTimeInterface $solvingTime,
        ?int $errorNumber,
        Example $example,
        callable $createAttemptResponse
    ) {
        $this->number = $number;
        $this->solvingTime = $solvingTime;
        $this->errorNumber = $errorNumber;
        $this->example = $example;
        $this->createAttemptResponse = $createAttemptResponse;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @Groups({Group::ATTEMPT, Group::EXAMPLE})
     */
    public function getString(): string
    {
        return $this->example->__toString();
    }

    /**
     * @Groups({Group::EXAMPLE})
     */
    public function getAnswer(): ?float
    {
        return $this->example->getAnswer();
    }

    /**
     * @Groups({Group::EXAMPLE})
     * @SerializedName("isRight")
     */
    public function isRight(): ?bool
    {
        return $this->example->isRight();
    }

    public function getSolvingTime(): ?\DateTimeInterface
    {
        return $this->solvingTime;
    }

    /**
     * @Groups({Group::EXAMPLE})
     */
    public function getSolvedAt(): ?\DateTimeInterface
    {
        return $this->example->getCreatedAt();
    }

    public function getErrorNumber(): ?int
    {
        return $this->errorNumber;
    }

    public function getExample(): Example
    {
        return $this->example;
    }

    /**
     * @Groups({Group::EXAMPLE})
     */
    public function getAttempt(): AttemptResponse
    {
        return \call_user_func($this->createAttemptResponse, $this->example->getAttempt());
    }
}
