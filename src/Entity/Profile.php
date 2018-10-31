<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProfileRepository")
 */
class Profile extends BaseProfile
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="profiles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPublic = false;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="profile")
     */
    private $users;

    public function __construct()
    {
        parent::__construct();
        $this->users = new ArrayCollection();
        $this->normalize();
    }

    public function getAuthor(): ? User
    {
        return $this->author;
    }

    public function setAuthor(? User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): self
    {
        $this->isPublic = $isPublic;

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

    private function normalizePercents()
    {
        $percentKeys = ['addPerc', 'subPerc', 'multPerc', 'divPerc'];
        $percents = [];
        $propertyAccessor = self::createPropertyAccessor();

        foreach ($percentKeys as $property) {
            $percents[$property] = $propertyAccessor->getValue($this, $property);
        }

        foreach (normPerc($percents) as $property => $value) {
            $propertyAccessor->setValue($this, $property, $value);
        }

        return $this;
    }

    public function getMinutes(): int
    {
        return $this->duration / MIN;
    }

    public function setMinutes(int $min)
    {
        $min = minVal(0, $min);
        $this->setDuration($min * MIN + $this->getSeconds());

        return $this;
    }

    public function getSeconds(): int
    {
        return $this->duration % MIN;
    }

    public function setSeconds(int $sec)
    {
        $sec = btwVal(0, 59, $sec);
        $this->setDuration($this->getMinutes() * MIN + $sec);

        return $this;
    }

    public function getSettings(): array
    {
        $this->normalize();

        return parent::getSettings();
    }

    public function __clone()
    {
        parent::__clone();
        $this->author = null;
        $this->isPublic = false;
    }

    public function normalize()
    {
        $this->normalizePercents();

        foreach (['add', 'sub', 'mult', 'div'] as $k) {
            foreach (['F', 'S'] as $n) {
                $min = $k.$n.'Min';
                $max = $k.$n.'Max';
                $this->$max = minVal($this->$min, $this->$max);
            }
        }

        if (0 === $this->divSMin) {
            $this->divSMin = 1;
        }
        $this->divSMax = minVal($this->divSMin, $this->divSMax);

        $addMin = $this->addFMin + $this->addSMin;
        $addMax = $this->addFMax + $this->addSMax;
        $this->addMin = btwVal($addMin, $addMax, $this->addMin, false);
        $this->addMax = btwVal($addMin, $addMax, $this->addMax, true);

        $subMin = $this->subFMin - $this->subSMax;
        $subMax = $this->subFMax - $this->subSMin;
        $this->subMin = btwVal($subMin, $subMax, $this->subMin, false);
        $this->subMax = btwVal($subMin, $subMax, $this->subMax, true);

        $multMin = $this->multFMin * $this->multSMin;
        $multMax = $this->multFMax * $this->multSMax;
        $this->multMin = btwVal($multMin, $multMax, $this->multMin, false);
        $this->multMax = btwVal($multMin, $multMax, $this->multMax, true);

        $divMin = $this->divFMin / $this->divSMax;
        $divMax = $this->divFMax / $this->divSMin;
        $this->divMin = ceil(btwVal($divMin, $divMax, $this->divMin, false));
        $this->divMax = btwVal($divMin, $divMax, $this->divMax, true);

        foreach (['add', 'sub', 'mult', 'div'] as $k) {
            foreach (['F', 'S', ''] as $n) {
                foreach (['Min', 'Max'] as $m) {
                    $v = $k.$n.$m;
                    $this->$v = (int) $this->$v;
                }
            }
        }

        return $this;
    }

    public function __toString()
    {
        return parent::__toString().' - '.$this->getAuthor()->getUsername();
    }
}
