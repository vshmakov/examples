<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\ApiPlatform\Filter\Example\TaskFilter;
use App\ApiPlatform\Filter\Example\UserFilter;
use App\DateTime\DateTime as DT;
use  App\Serializer\Group;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExampleRepository")
 * @TODO deny item access
 * @ApiResource(
 *     order={"id": "DESC"},
 *     normalizationContext={"groups"={Group::EXAMPLE}},
 *     itemOperations={"get"},
 *     collectionOperations={"get"}
 * )
 * @ApiFilter(  UserFilter::class)
 * @ApiFilter(TaskFilter::class)
 */
class Example
{
    public const ACTION_NAMES = [1 => 'add', 'sub', 'mult', 'div'];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Attempt", inversedBy="examples")
     * @ORM\JoinColumn(nullable=false)
     */
    private $attempt;

    /**
     * @ORM\Column(type="float")
     */
    private $first;

    /**
     * @ORM\Column(type="smallint")
     */
    private $sign;

    /**
     * @ORM\Column(type="float")
     */
    private $second;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $answer;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isRight;

    /**
     * @ORM\Column(type="datetime")
     */
    private $addTime;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $answerTime;

    public function __construct()
    {
        $this->addTime = new \DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAttempt(): ?Attempt
    {
        return $this->attempt;
    }

    public function setAttempt(? Attempt $attempt): self
    {
        $this->attempt = $attempt;

        return $this;
    }

    public function getFirst(): ?float
    {
        return $this->first;
    }

    public function setFirst(float $first): self
    {
        $this->first = $first;

        return $this;
    }

    public function getSign(): ?int
    {
        return $this->sign;
    }

    public function setSign(int $sign): self
    {
        $this->sign = $sign;

        return $this;
    }

    public function getSecond(): ?float
    {
        return $this->second;
    }

    public function setSecond(float $second): self
    {
        $this->second = $second;

        return $this;
    }

    public function getAnswer(): ?float
    {
        return $this->answer;
    }

    public function setAnswer(? float $answer): void
    {
        $this->answer = $answer;
        $this->setAnswerTime(new \DateTime());
    }

    public function isRight(): ?bool
    {
        return $this->isRight;
    }

    public function setIsRight(? bool $isRight): self
    {
        $this->isRight = $isRight;

        return $this;
    }

    public function getAddTime(): ?\DateTimeInterface
    {
        return DT::createFromDT($this->addTime);
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->getAddTime();
    }

    public function setAddTime(\DateTimeInterface $addTime): self
    {
        $this->addTime = $addTime;

        return $this;
    }

    public function getAnswerTime(): ?\DateTimeInterface
    {
        return null !== $this->answerTime ? DT::createFromDT($this->answerTime) : null;
    }

    public function setAnswerTime(\DateTimeInterface $answerTime): self
    {
        $this->answerTime = $answerTime;

        return $this;
    }

    public function __toString()
    {
        return sprintf('%s %s %s', $this->first, [1 => '+', '-', '*', ':'][$this->sign], $this->second);
    }

    public function isAnswered()
    {
        return null !== $this->answer;
    }
}
