<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SettingsRepository")
 */
class Settings extends BaseProfile
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;
    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Task", mappedBy="settings", cascade={"persist", "remove"})
     */
    private $task;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Attempt", mappedBy="settings")
     */
    private $attempts;

    public function __construct()
    {
        parent::__construct();
        $this->attempts = new ArrayCollection();
    }

    public function getTask(): ?Task
    {
        return $this->task;
    }

    public function setTask(Task $task): self
    {
        $this->task = $task;

        // set the owning side of the relation if necessary
        if ($this !== $task->getSettings()) {
            $task->setSettings($this);
        }

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
            $attempt->setSettings($this);
        }

        return $this;
    }

    public function removeAttempt(Attempt $attempt): self
    {
        if ($this->attempts->contains($attempt)) {
            $this->attempts->removeElement($attempt);
            // set the owning side to null (unless already changed)
            if ($attempt->getSettings() === $this) {
                $attempt->setSettings(null);
            }
        }

        return $this;
    }
}
