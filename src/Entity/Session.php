<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Service\IpInformer as IpInf;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SessionRepository")
 */
class Session
{
use DTTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $addTime;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lastTime;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $sid;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="sessions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Attempt", mappedBy="session", orphanRemoval=true)
     */
    private $attempts;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $pageCount=0;

    public function __construct()
    {
        $this->attempts = new ArrayCollection();
$this->initAddTime();
$this->lastTime=new \DateTime;
    }

    public function getId()
    {
        return $this->id;
    }

public function getLastTime() {
return $this->dt($this->lastTime);
}

public function setLastTime($dt) {
$this->lastTime=$dt;
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

    public function getSid(): ?string
    {
        return $this->sid;
    }

    public function setSid(string $sid): self
    {
        $this->sid = $sid;

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
            $attempt->setSession($this);
        }

        return $this;
    }

    public function removeAttempt(Attempt $attempt): self
    {
        if ($this->attempts->contains($attempt)) {
            $this->attempts->removeElement($attempt);
            // set the owning side to null (unless already changed)
            if ($attempt->getSession() === $this) {
                $attempt->setSession(null);
            }
        }

        return $this;
    }

public function getIpInfo() {
static $a=[];
$ip=$this->sid;
return $a[$ip] ?? $a[$ip]=IpInf::getInfoByIp($ip);
}

public function getPageCount(): int
{
    return $this->pageCount ?? 0;
}

public function setPageCount(int $pageCount): self
{
    $this->pageCount = $pageCount;

    return $this;
}

public function incPageCount() {
$this->pageCount=((int) $this->pageCount) + 1;
return $this;
}
}
