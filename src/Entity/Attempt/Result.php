<?php

namespace App\Entity\Attempt;

use  App\DateTime\DateTime as DT;
use  App\Entity\Attempt;
use  App\Serializer\Group;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity
 */
class Result
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Attempt
     * @ORM\OneToOne(targetEntity="App\Entity\Attempt", inversedBy="result", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $attempt;

    /**
     * @var \DateTimeInterface
     * @ORM\Column(type="datetime")
     * @Groups({Group::ATTEMPT})
     */
    private $finishedAt;

    /**
     * @var int
     * @ORM\Column(type="smallint")
     * @Groups({Group::ATTEMPT})
     */
    private $errorsCount;

    /**
     * @var int
     * @ORM\Column(type="smallint")
     * @Groups({Group::ATTEMPT})
     */
    private $rating;

    /**
     * @var int
     * @ORM\Column(type="smallint")
     * @Groups({Group::ATTEMPT})
     */
    private $solvedExamplesCount;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAttempt(): ?Attempt
    {
        return $this->attempt;
    }

    public function setAttempt(Attempt $attempt): void
    {
        $this->attempt = $attempt;
    }

    public function getErrorsCount(): ?int
    {
        return $this->errorsCount;
    }

    public function setErrorsCount(int $errorsCount): void
    {
        $this->errorsCount = $errorsCount;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): void
    {
        $this->rating = $rating;
    }

    public function getSolvedExamplesCount(): ?int
    {
        return $this->solvedExamplesCount;
    }

    public function setSolvedExamplesCount(int $solvedExamplesCount): void
    {
        $this->solvedExamplesCount = $solvedExamplesCount;
    }

    public function getFinishedAt(): \DateTimeInterface
    {
        return DT::createFromDT($this->finishedAt);
    }

    public function setFinishedAt(\DateTimeInterface $finishedAt): void
    {
        $this->finishedAt = $finishedAt;
    }

    /**
     * @Groups({Group::ATTEMPT})
     */
    public function getRemainedExamplesCount(): int
    {
        return $this->attempt->getSettings()->getExamplesCount() - $this->getSolvedExamplesCount();
    }

    /**
     * @Groups({Group::ATTEMPT})
     */
    public function getSolvingTime(): \DateTimeInterface
    {
        return DT::createByDifferent($this->getAttempt()->getAddTime(), $this->getFinishedAt());
    }

    /**
     * @Groups({Group::ATTEMPT})
     * @SerializedName("isFinished")
     */
    public function isFinished(): bool
    {
        return time() > $this->getAttempt()->getLimitTime()->getTimestamp()
            or 0 === $this->getRemainedExamplesCount();
    }

    public function getTimePerExample(): ?\DateTimeInterface
    {
        $solvedExamplesCount = $this->getSolvedExamplesCount();

        if (0 === $solvedExamplesCount) {
            return null;
        }

        return DT::createFromTimestamp(
            $this->getSolvingTime()->getTimestamp() / $solvedExamplesCount
        );
    }
}
