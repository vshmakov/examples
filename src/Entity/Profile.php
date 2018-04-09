<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProfileRepository")
 */
class Profile
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="profiles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\Column(type="datetime")
     */
    private $addTime;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPublic;

    /**
     * @ORM\Column(type="smallint")
     */
    private $duration;

    /**
     * @ORM\Column(type="smallint")
     */
    private $examplesCount;

    /**
     * @ORM\Column(type="integer")
     */
    private $addMin;

    /**
     * @ORM\Column(type="integer")
     */
    private $addMax;

    /**
     * @ORM\Column(type="integer")
     */
    private $subMin;

    /**
     * @ORM\Column(type="integer")
     */
    private $subMax;

    /**
     * @ORM\Column(type="integer")
     */
    private $minSub;

    /**
     * @ORM\Column(type="integer")
     */
    private $multMin;

    /**
     * @ORM\Column(type="integer")
     */
    private $multMax;

    /**
     * @ORM\Column(type="integer")
     */
    private $divMin;

    /**
     * @ORM\Column(type="integer")
     */
    private $divMax;

    /**
     * @ORM\Column(type="integer")
     */
    private $minDiv;

    public function getId()
    {
        return $this->id;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getAddTime(): ?\DateTimeInterface
    {
        return $this->addTime;
    }

    public function setAddTime(\DateTimeInterface $addTime): self
    {
        $this->addTime = $addTime;

        return $this;
    }

    public function getIsPublic(): ?bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): self
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getExamplesCount(): ?int
    {
        return $this->examplesCount;
    }

    public function setExamplesCount(int $examplesCount): self
    {
        $this->examplesCount = $examplesCount;

        return $this;
    }

    public function getAddMin(): ?int
    {
        return $this->addMin;
    }

    public function setAddMin(int $addMin): self
    {
        $this->addMin = $addMin;

        return $this;
    }

    public function getAddMax(): ?int
    {
        return $this->addMax;
    }

    public function setAddMax(int $addMax): self
    {
        $this->addMax = $addMax;

        return $this;
    }

    public function getSubMin(): ?int
    {
        return $this->subMin;
    }

    public function setSubMin(int $subMin): self
    {
        $this->subMin = $subMin;

        return $this;
    }

    public function getSubMax(): ?int
    {
        return $this->subMax;
    }

    public function setSubMax(int $subMax): self
    {
        $this->subMax = $subMax;

        return $this;
    }

    public function getMinSub(): ?int
    {
        return $this->minSub;
    }

    public function setMinSub(int $minSub): self
    {
        $this->minSub = $minSub;

        return $this;
    }

    public function getMultMin(): ?int
    {
        return $this->multMin;
    }

    public function setMultMin(int $multMin): self
    {
        $this->multMin = $multMin;

        return $this;
    }

    public function getMultMax(): ?int
    {
        return $this->multMax;
    }

    public function setMultMax(int $multMax): self
    {
        $this->multMax = $multMax;

        return $this;
    }

    public function getDivMin(): ?int
    {
        return $this->divMin;
    }

    public function setDivMin(int $divMin): self
    {
        $this->divMin = $divMin;

        return $this;
    }

    public function getDivMax(): ?int
    {
        return $this->divMax;
    }

    public function setDivMax(int $divMax): self
    {
        $this->divMax = $divMax;

        return $this;
    }

    public function getMinDiv(): ?int
    {
        return $this->minDiv;
    }

    public function setMinDiv(int $minDiv): self
    {
        $this->minDiv = $minDiv;

        return $this;
    }
}
