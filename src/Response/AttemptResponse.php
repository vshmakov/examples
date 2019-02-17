<?php

namespace App\Response;

final class AttemptResponse
{
    /**
     * @var int
     */
    private $number;

    /**
     * @var bool
     */
    private $isFinished;

    /**
     * @var ExampleResponse|null
     */
    private $example;

    /**
     * @var \DateTimeInterface
     */
    private $limitTime;

    /**
     * @var int
     */
    private $errorsCount;

    /**
     * @var int
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
