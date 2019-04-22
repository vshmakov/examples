<?php

declare(strict_types=1);

namespace App\Entity\User;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class SocialAccount
{
    use CreatedAtTrait;

    /**
     * @var int|null
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column
     * @Assert\NotNull
     */
    private $network;

    /**
     * @var string|null
     * @ORM\Column
     * @Assert\NotNull
     */
    private $networkId;

    /**
     * @var string|null
     * @ORM\Column(nullable=true)
     */
    private $firstName;

    /**
     * @var string|null
     * @ORM\Column(nullable=true)
     */
    private $lastName;

    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="socialAccounts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @var string|null
     * @ORM\Column(nullable=true)
     */
    private $profile;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getNetwork(): ?string
    {
        return $this->network;
    }

    public function setNetwork(?string $network): void
    {
        $this->network = $network;
    }

    /**
     * @return string|null
     */
    public function getNetworkId(): ?string
    {
        return $this->networkId;
    }

    /**
     * @param string|null $networkId
     */
    public function setNetworkId(?string $networkId): void
    {
        $this->networkId = $networkId;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getUsername(): string
    {
        return sprintf('^%s-%s', $this->getNetwork(), $this->getNetworkId());
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getProfile(): ?string
    {
        return $this->profile;
    }

    public function setProfile(?string $profile): void
    {
        $this->profile = $profile;
    }
}
