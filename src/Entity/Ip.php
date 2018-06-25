<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Service\IpInformer as IpInfo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\IpRepository")
 */
class Ip
{
use BaseTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=16)
     */
    private $ip;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $region;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="datetime")
     */
    private $addTime;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="ips")
     */
private $users;


public function __construct() {
$this->addTime=new \DateTime;
}

    public function getId()
    {
        return $this->id;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

public function isValid() {
return IpInfo::isIp($this->ip);
}

    public function setIp(string $ip): self
    {
if (IpInfo::isIp($ip)) {
        $this->ip = $ip;
$info=IpInfo::getInfoByIp($ip);
$this->country=$info["countryName"];
$this->region=$info["stateProv"];
$this->city=$info["city"];
}

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

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

public function __toString() {
return $this->ip;
}

public function getUsers(): Collection
{
    return $this->users;
}

public function addUser(User $user): self
{
    if (!$this->users->contains($user)) {
        $this->users[] = $user;
    }

    return $this;
}

public function removeUser(User $user): self
{
    if ($this->users->contains($user)) {
        $this->users->removeElement($user);
    }

    return $this;
}

}