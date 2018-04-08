<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AttemptRepository")
 */
class Attempt
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Example", mappedBy="attempt", orphanRemoval=true)
     */
    private $examples;

    /**
     * @ORM\Column(type="datetime")
     */
    private $addTime;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="attempts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="object")
     */
    private $settings;

    public function __construct()
    {
        $this->examples = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Collection|Example[]
     */
    public function getExamples(): Collection
    {
        return $this->examples;
    }

    public function addExample(Example $example): self
    {
        if (!$this->examples->contains($example)) {
            $this->examples[] = $example;
            $example->setAttempt($this);
        }

        return $this;
    }

    public function removeExample(Example $example): self
    {
        if ($this->examples->contains($example)) {
            $this->examples->removeElement($example);
            // set the owning side to null (unless already changed)
            if ($example->getAttempt() === $this) {
                $example->setAttempt(null);
            }
        }

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function setSettings($settings): self
    {
        $this->settings = $settings;

        return $this;
    }
}
