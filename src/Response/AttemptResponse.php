<?php

namespace App\Response;

use Symfony\Component\Serializer\Annotation\Groups;

final class AttemptResponse
{
    /**
     * @var int
     * @Groups({"attempt"})
     */
    private $number;

    /**
     * @var bool
     * @Groups({"attempt"})
     */
    private $isFinished;

    /**
     * @var ExampleResponse|null
     * @Groups({"attempt"})
     */
    private $example;

    /**
     * @var \DateTimeInterface
     * @Groups({"attempt"})
     */
    private $limitTime;

    /**
     * @var int
     * @Groups({"attempt"})
     */
    private $errorsCount;

    /**
     * @var int
     * @Groups({"attempt"})
     */
    private $remainedExamplesCount;

    public function __construct(
        int $number,
        bool $isFinished,
        ?ExampleResponse $example,
        \DateTimeInterface $limitTime,
        int $errorsCount,
        int $remainedExamplesCount
    ) {
        $this->number = $number;
        $this->isFinished = $isFinished;
        $this->example = $example;
        $this->limitTime = $limitTime;
        $this->errorsCount = $errorsCount;
        $this->remainedExamplesCount = $remainedExamplesCount;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function isFinished(): bool
    {
        return $this->isFinished;
    }

    public function getExample(): ?ExampleResponse
    {
        return $this->example;
    }

    public function getLimitTime(): \DateTimeInterface
    {
        return $this->limitTime;
    }

    public function getErrorsCount(): int
    {
        return $this->errorsCount;
    }

    public function getRemainedExamplesCount(): int
    {
        return $this->remainedExamplesCount;
    }
}
