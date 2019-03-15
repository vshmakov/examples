<?php

namespace App\Response;

use  App\Entity\Attempt;
use App\Entity\Attempt\Result;
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
     * @var Attempt
     */
    private $attempt;

    /** @var string */
    private $title;

    public function __construct(
        int $number,
        string $title,
        bool $isFinished,
        ?ExampleResponse $example,
        Attempt $attempt
    ) {
        $this->number = $number;
        $this->title = $title;
        $this->isFinished = $isFinished;
        $this->example = $example;
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

    /**
     * @Groups({Group::ATTEMPT})
     */
    public function getResult(): Result
    {
        return $this->attempt->getResult();
    }

    /**
     * @Groups({Group::ATTEMPT})
     */
    public function getId(): int
    {
        return $this->attempt->getId();
    }

    /**
     * @Groups({Group::ATTEMPT})
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @Groups({Group::ATTEMPT})
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->attempt->getAddTime();
    }
}
