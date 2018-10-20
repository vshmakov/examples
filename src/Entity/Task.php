<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TaskRepository")
 */
class Task
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="tasks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="homework")
     */
    private $contractors;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Settings", inversedBy="task", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $settings;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Attempt", mappedBy="task")
     */
    private $attempts;

    /**
     * @ORM\Column(type="smallint")
     */
    private $timesCount;

    public function __construct()
    {
        $this->contractors = new ArrayCollection();
        $this->attempts = new ArrayCollection();
    }

    public function getId(): ?int
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

    /**
     * @return Collection|User[]
     */
    public function getContractors(): Collection
    {
        return $this->contractors;
    }

    public function addContractor(User $contractor): self
    {
        if (!$this->contractors->contains($contractor)) {
            $this->contractors[] = $contractor;
        }

        return $this;
    }

    public function removeContractor(User $contractor): self
    {
        if ($this->contractors->contains($contractor)) {
            $this->contractors->removeElement($contractor);
        }

        return $this;
    }

    public function getSettings(): ?Settings
    {
        return $this->settings;
    }

    public function setSettings(Settings $settings): self
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * @return Collection|Attempt[]
     */
    public function getAttempts(): Collection
    {
        return $this->attempts;
    }

    public function addAttempt(Attempt $attempt): self
    {
        if (!$this->attempts->contains($attempt)) {
            $this->attempts[] = $attempt;
            $attempt->setTask($this);
        }

        return $this;
    }

    public function removeAttempt(Attempt $attempt): self
    {
        if ($this->attempts->contains($attempt)) {
            $this->attempts->removeElement($attempt);
            // set the owning side to null (unless already changed)
            if ($attempt->getTask() === $this) {
                $attempt->setTask(null);
            }
        }

        return $this;
    }

    public function getTimesCount(): ?int
    {
        return $this->timesCount;
    }

    public function setTimesCount(int $timesCount): self
    {
        $this->timesCount = $timesCount;

        return $this;
    }
}