<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TransferRepository")
 */
class Transfer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $label;

    /**
     * @ORM\Column(type="datetime")
     */
    private $addTime;

    /**
     * @ORM\Column(type="boolean")
     */
    private $held = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $heldTime;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="transfers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $money;

    public function __construct()
    {
        $this->addTime = new \DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

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

    public function getHeld(): ?bool
    {
        return $this->held;
    }

    public function setHeld(bool $held): self
    {
        $this->held = $held;

        return $this;
    }

    public function getHeldTime(): ?\DateTimeInterface
    {
        return $this->heldTime;
    }

    public function setHeldTime(?\DateTimeInterface $heldTime): self
    {
        $this->heldTime = $heldTime;

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

    public function getMoney(): ?float
    {
        return $this->money;
    }

    public function setMoney(?float $money): self
    {
        $this->money = $money;

        return $this;
    }
}
