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
}
