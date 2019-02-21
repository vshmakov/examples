<?php

namespace App\Response;

use App\Entity\Settings;
use App\Serializer\Group;
use Symfony\Component\Serializer\Annotation\Groups;

final class AttemptResponse
{
    /**
     * @var int
     *@Groups({Group::ATTEMPT})
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
     * @var \DateTimeInterface
     * @Groups({Group::ATTEMPT})
     */
    private $limitTime;

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
     * @var Settings
     * @Groups({Group::ATTEMPT})
     */
    private $settings;

    public function __construct(
        int $number,
        bool $isFinished,
        ?ExampleResponse $example,
        \DateTimeInterface $limitTime,
        int $errorsCount,
        int $remainedExamplesCount,
        Settings $settings
    ) {
        $this->number = $number;
        $this->isFinished = $isFinished;
        $this->example = $example;
        $this->limitTime = $limitTime;
        $this->errorsCount = $errorsCount;
        $this->remainedExamplesCount = $remainedExamplesCount;
        $this->settings = $settings;
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

    public function getSettings(): Settings
    {
        return $this->settings;
    }
}
