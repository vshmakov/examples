<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProfileRepository")
 */
class Profile
{
use DTTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="profiles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\Column(type="datetime")
     */
    private $addTime;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPublic=false;

    /**
     * @ORM\Column(type="smallint")
     */
    private $duration=180;

    /**
     * @ORM\Column(type="smallint")
     */
    private $examplesCount=5;

    /**
     * @ORM\Column(type="integer")
     */
    private $addFMin=0;

    /**
     * @ORM\Column(type="integer")
     */
    private $addFMax=3;

    /**
     * @ORM\Column(type="integer")
     */
    private $addSMin=0;

    /**
     * @ORM\Column(type="integer")
     */
    private $addSMax=3;

    /**
     * @ORM\Column(type="integer")
     */
    private $addMin=-1;

    /**
     * @ORM\Column(type="integer")
     */
    private $addMax=-1;

    /**
     * @ORM\Column(type="integer")
     */
    private $subFMin=0;

    /**
     * @ORM\Column(type="integer")
     */
    private $subFMax=5;

    /**
     * @ORM\Column(type="integer")
     */
    private $subSMin=0;

    /**
     * @ORM\Column(type="integer")
     */
    private $subSMax=5;

    /**
     * @ORM\Column(type="integer")
     */
    private $subMin=0;

    /**
     * @ORM\Column(type="integer")
     */
    private $subMax=1000;

    /**
     * @ORM\Column(type="integer")
     */
    private $multFMin=0;

    /**
     * @ORM\Column(type="integer")
     */
    private $multFMax=3;

    /**
     * @ORM\Column(type="integer")
     */
    private $multSMin=0;

    /**
     * @ORM\Column(type="integer")
     */
    private $multSMax=3;

    /**
     * @ORM\Column(type="integer")
     */
    private $multMin=-1;

    /**
     * @ORM\Column(type="integer")
     */
    private $multMax=-1;

    /**
     * @ORM\Column(type="integer")
     */
    private $divFMin=0;

    /**
     * @ORM\Column(type="integer")
     */
    private $divFMax=6;

    /**
     * @ORM\Column(type="integer")
     */
    private $divSMin=1;

    /**
     * @ORM\Column(type="integer")
     */
    private $divSMax=6;

    /**
     * @ORM\Column(type="integer")
     */
    private $divMin=-1;

    /**
     * @ORM\Column(type="integer")
     */
    private $divMax=-1;

    /**
     * @ORM\Column(type="smallint")
     */
    private $addPerc=25;

   /**
     * @ORM\Column(type="smallint")
     */
    private $subPerc=25;

    /**
     * @ORM\Column(type="smallint")
     */
    private $multPerc=25;

    /**
     * @ORM\Column(type="smallint")
     */
    private $divPerc=25;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="profile")
     */
    private $users;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDemanding=true;

    public function __construct()
    {
        $this->users = new ArrayCollection();
$this->initAddTime();
$this->normData();
    }

    public function getId()
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

    public function getAddTime(): ?\DateTimeInterface
    {
        return $this->dt($this->addTime);
    }

    public function setAddTime(\DateTimeInterface $addTime): self
    {
        $this->addTime = $addTime;

        return $this;
    }

    public function isPublic(): ?bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): self
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = btwVal(30, HOUR-1, $duration);

        return $this;
    }

    public function getExamplesCount(): ?int
    {
        return $this->examplesCount;
    }

    public function setExamplesCount(int $examplesCount): self
    {
        $this->examplesCount = btwVal(3, 150, $examplesCount);

        return $this;
    }

    public function getAddPerc(): ?int
    {
        return $this->addPerc;
    }

    public function setAddPerc(int $addPerc): self
    {
        $this->addPerc = $addPerc;

        return $this;
    }

    public function getSubPerc(): ?int
    {
        return $this->subPerc;
    }

    public function setSubPerc(int $subPerc): self
    {
        $this->subPerc = $subPerc;

        return $this;
    }

    public function getMultPerc(): ?int
    {
        return $this->multPerc;
    }

    public function setMultPerc(int $multPerc): self
    {
        $this->multPerc = $multPerc;

        return $this;
    }

    public function getDivPerc(): ?int
    {
        return $this->divPerc;
    }

    public function setDivPerc(int $divPerc): self
    {
        $this->divPerc = $divPerc;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setProfile($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getProfile() === $this) {
                $user->setProfile(null);
            }
        }

        return $this;
    }

private function normPerc() {
$pKeies=['addPerc', 'subPerc', 'multPerc', 'divPerc'];
$p=[];
foreach ($pKeies as $k) {
$p[$k]=$this->$k;
}

foreach (normPerc($p) as $k=>$v) {
$this->$k=$v;
}

return $this;
}

public function getMinutes():int {
return ($this->duration/MIN);
}

public function setMinutes(int $min) {
$min=minVal(0, $min);
$this->setDuration($min*MIN+$this->getSeconds());
return $this;
}

public function getSeconds():int {
return $this->duration%MIN;
}

public function setSeconds(int $sec) {
$sec=btwVal(0, 59, $sec);
$this->setDuration($this->getMinutes()*MIN+$sec);
return $this;
}

public function getDescription(): ?string
{
    return $this->description;
}

public function setDescription(string $description): self
{
    $this->description = $description;

    return $this;
}

public function __toString() {
return $this->getDescription()." - ".$this->getAuthor()->getUsername();
}

public function getData() {
$this->normData();
$d=[];
$f=getArrByStr("duration examplesCount addFMin addFMax addSMin addSMax addMin addMax subFMin subFMax subSMin subSMax subMin subMax multFMin multFMax multSMin multSMax multMin multMax divFMin divFMax divSMin divSMax divMin divMax addPerc subPerc multPerc divPerc");

foreach ($this as $k=>$v) {
if (in_array($k, $f)) $d[$k]=$v;
}

return $d;
}

public function isDemanding(): ?bool
{
    return $this->isDemanding;
}

public function setIsDemanding(bool $isDemanding): self
{
    $this->isDemanding = $isDemanding;
    return $this;
}

public function getInstance() {
return $this;
}

public function __clone() {
$this->id=null;
$this->author=null;
$this->isPublic=false;
$this->initAddTime();
}

public function normData() {
$this->normPerc();

foreach (["add", "sub", "mult", "div"] as $k) {
foreach (["F", "S"] as $n) {
$min=$k.$n."Min";
$max=$k.$n."Max";
$this->$max=minVal($this->$min, $this->$max);
}
}
if ($this->divSMin == 0) $this->divSMin=1;
$this->divSMax=minVal($this->divSMin, $this->divSMax);

$addMin=$this->addFMin + $this->addSMin;
$addMax=$this->addFMax + $this->addSMax;
$this->addMin=btwVal($addMin, $addMax, $this->addMin, false);
$this->addMax=btwVal($addMin, $addMax, $this->addMax, true);

$subMin=$this->subFMin - $this->subSMax;
$subMax=$this->subFMax - $this->subSMin;
$this->subMin=btwVal($subMin, $subMax, $this->subMin, false);
$this->subMax=btwVal($subMin, $subMax, $this->subMax, true);

$multMin=$this->multFMin * $this->multSMin;
$multMax=$this->multFMax * $this->multSMax;
$this->multMin=btwVal($multMin, $multMax, $this->multMin, false);
$this->multMax=btwVal($multMin, $multMax, $this->multMax, true);

$divMin=$this->divFMin / $this->divSMax;
$divMax=$this->divFMax / $this->divSMin;
$this->divMin=ceil(btwVal($divMin, $divMax, $this->divMin, false));
$this->divMax=btwVal($divMin, $divMax, $this->divMax, true);

foreach (["add", "sub", "mult", "div"] as $k) {
foreach (["F", "S", ""] as $n) {
foreach (["Min", "Max"] as $m) {
$v=$k.$n.$m;
$this->$v=(int) $this->$v;
}
}
}

return $this;
}
}
