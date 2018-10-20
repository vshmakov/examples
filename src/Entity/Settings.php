<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SettingsRepository")
 */
class Settings extends ProfileBase
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Attempt", mappedBy="settings", cascade={"persist", "remove"})
     */
    private $attempt;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Task", mappedBy="settings", cascade={"persist", "remove"})
     */
    private $task;

    public function getAttempt(): ?Attempt
    {
        return $this->attempt;
    }

    public function setAttempt(Attempt $attempt): self
    {
        $this->attempt = $attempt;

        // set the owning side of the relation if necessary
        if ($this !== $attempt->getSettings()) {
            $attempt->setSettings($this);
        }

        return $this;
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
}
