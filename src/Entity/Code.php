<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\BaseTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CodeRepository")
 */
class Code
{
    use BaseTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $string;

    /**
     * @ORM\Column(type="datetime")
     */
    private $addTime;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $activateTime;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="codes")
     */
    private $user;

    /**
     * @ORM\Column(type="boolean")
     */
    private $activated = false;

    /**
     * @ORM\Column(type="smallint")
     */
    private $money = 100;

    public function __construct()
    {
    }

    public function getId()
    {
        return $this->id;
    }

    public function getString(): ?string
    {
        return $this->string;
    }

    public function setString(string $string): self
    {
        $this->string = $string;

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

    public function getActivateTime(): ?\DateTimeInterface
    {
        return $this->activateTime;
    }

    public function setActivateTime(?\DateTimeInterface $activateTime): self
    {
        $this->activateTime = $activateTime;

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

    public function getActivated(): ?bool
    {
        return $this->activated;
    }

    public function setActivated(bool $activated): self
    {
        $this->activated = $activated;

        return $this;
    }

    public function getMoney(): ?int
    {
        return $this->money;
    }

    public function setMoney(int $money): self
    {
        $this->money = $money;

        return $this;
    }
}
