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
    use BaseTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="bigint")
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Session", inversedBy="attempts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $session;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Task", inversedBy="attempts")
     */
    private $task;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Settings", inversedBy="attempts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $settings;

    public function __construct()
    {
        $this->examples = new ArrayCollection();
        $this->addTime = new \DateTime();
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
        return $this->dt($this->addTime);
    }

    public function setAddTime(\DateTimeInterface $addTime): self
    {
        $this->addTime = $addTime;

        return $this;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(? Session $session): self
    {
        $this->session = $session;

        return $this;
    }

    public function getExamplesCount()
    {
        return $this->getSettings()->getExamplesCount();
    }

    public function getLimitTime(): \DateTimeInterface
    {
        return $this->dts($this->getAddTime()->getTimestamp() + $this->getSettings()->getDuration());
    }

    public function getMaxTime()
    {
        return $this->dts($this->getSettings()->getDuration());
    }

    public function getUser()
    {
        return $this->getSession()->getUser();
    }

    public function getTask(): ?Task
    {
        return $this->task;
    }

    public function setTask(?Task $task): self
    {
        $this->task = $task;

        return $this;
    }

    public function getSettings(): ?Settings
    {
        return $this->settings;
    }

    public function setSettings(?Settings $settings): self
    {
        $this->settings = $settings;

        return $this;
    }
}
