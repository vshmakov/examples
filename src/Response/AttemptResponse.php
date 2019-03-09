<?php

namespace App\Response;

use App\Entity\Attempt;
use App\Entity\Settings;
use App\Entity\User;
use App\Serializer\Group;
use Symfony\Component\Serializer\Annotation\Groups;

final class AttemptResponse
{
    /**
     * @var int
     * @Groups({Group::ATTEMPT})
     */
    private $number;

    /**
     * @var bool
     * @Groups({Group::ATTEMPT})
     */
    private $isFinished;

    /**
     * @var ExampleResponse|null
     * @Groups({Group::ATTEMPT})
     */
    private $example;

    /**
     * @var int
     * @Groups({Group::ATTEMPT})
     */
    private $errorsCount;

    /**
     * @var int
     * @Groups({Group::ATTEMPT})
     */
    private $remainedExamplesCount;

    /**
     * @var Attempt
     */
    private $attempt;

    public function __construct(
        int $number,
        bool $isFinished,
        ?ExampleResponse $example,
        int $errorsCount,
        int $remainedExamplesCount,
        Attempt $attempt
    ) {
        $this->number = $number;
        $this->isFinished = $isFinished;
        $this->example = $example;
        $this->errorsCount = $errorsCount;
        $this->remainedExamplesCount = $remainedExamplesCount;
        $this->attempt = $attempt;
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

    /**
     * @Groups({Group::ATTEMPT})
     */
    public function getLimitTime(): \DateTimeInterface
    {
        return $this->attempt->getLimitTime();
    }

    public function getErrorsCount(): int
    {
        return $this->errorsCount;
    }

    public function getRemainedExamplesCount(): int
    {
        return $this->remainedExamplesCount;
    }

    /**
     * @Groups({Group::ATTEMPT})
     */
    public function getSettings(): Settings
    {
        return $this->attempt->getSettings();
    }

    /**
     * @Groups({Group::ATTEMPT})
     */
    public function getUser(): User
    {
        return $this->attempt->getUser();
    }
}
