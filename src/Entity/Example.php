<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\DT;
use App\Service\ExampleManager as ExMNG;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExampleRepository")
 */
class Example
{
use DTTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
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
     * @ORM\Column(type="datetime")
     */
    private $answerTime;

    public function getId()
    {
        return $this->id;
    }

    public function getAttempt(): ?Attempt
    {
        return $this->attempt;
    }

    public function setAttempt(?Attempt $attempt): self
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

    public function setAnswer(?float $answer): self
    {
        $this->answer = $answer;
$this->setIsRight($answer === ExMng::solve(($this->first, $this->second, $this->sign));
$this->setAnswerTime(new \DateTime());
        return $this;
    }

    public function getIsRight(): ?bool
    {
        return $this->isRight;
    }

    public function setIsRight(?bool $isRight): self
    {
        $this->isRight = $isRight;

        return $this;
    }

    public function getAddTime(): ?\DateTimeInterface
    {
        return DT::createFromDT($this->addTime);
    }

    public function setAddTime(\DateTimeInterface $addTime): self
    {
        $this->addTime = $addTime;

        return $this;
    }

    public function getAnswerTime(): ?\DateTimeInterface
    {
        return DT::createFromDT($this->answerTime);
    }

    public function setAnswerTime(\DateTimeInterface $answerTime): self
    {
        $this->answerTime = $answerTime;

        return $this;
    }

public function __toString() {
return sprintf("%s %s %s", $this->first, [1=>"+", "-", "*", ":"][$this->sign], $this->second);
}

}