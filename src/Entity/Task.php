<?php

namespace App\Entity;

use App\DateTime\DateTime as DT;
use  App\Repository\TaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Entity(repositoryClass=TaskRepository::class)
 */
class Task
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="tasks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="homework")
     */
    private $contractors;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Attempt", mappedBy="task")
     */
    private $attempts;

    /**
     * @ORM\Column(type="smallint")
     */
    private $timesCount = 5;

    /**
     * @ORM\Column(type="datetime")
     */
    private $addTime;

    /**
     * @ORM\Column(type="datetime")
     */
    private $limitTime;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Settings", inversedBy="tasks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $settings;

    public function __construct()
    {
        $this->contractors = new ArrayCollection();
        $this->attempts = new ArrayCollection();
        $this->addTime = new \DateTime();
        $this->limitTime = (new \DateTime())->add(new \DateInterval('P7D'));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(User $author): void
    {
        Assert::true($author->isTeacher());
        $this->author = $author;
        $this->setContractors($author->getStudents());
    }

    /**
     * @return Collection|User[]
     */
    public function getContractors(): Collection
    {
        return $this->contractors;
    }

    public function addContractor(User $contractor): self
    {
        if (!$this->contractors->contains($contractor)) {
            $this->contractors[] = $contractor;
        }

        return $this;
    }

    public function removeContractor(User $contractor): self
    {
        if ($this->contractors->contains($contractor)) {
            $this->contractors->removeElement($contractor);
        }

        return $this;
    }

    public function setContractors(Collection $contractors): void
    {
        $this->contractors = $contractors;
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
            $attempt->setTask($this);
        }

        return $this;
    }

    public function removeAttempt(Attempt $attempt): self
    {
        if ($this->attempts->contains($attempt)) {
            $this->attempts->removeElement($attempt);
            // set the owning side to null (unless already changed)
            if ($attempt->getTask() === $this) {
                $attempt->setTask(null);
            }
        }

        return $this;
    }

    public function getTimesCount(): ?int
    {
        return $this->timesCount;
    }

    public function setTimesCount(int $timesCount): self
    {
        $this->timesCount = $timesCount;

        return $this;
    }

    public function getAddTime(): ?\DateTimeInterface
    {
        return DT::createFromDT($this->addTime);
    }

    public function setAddTime(\DateTimeInterface $addTime): self
    {
        $this->addTime = $addTime;

        return $this;
    }

    public function getLimitTime(): ?\DateTimeInterface
    {
        return DT::createFromDT($this->limitTime);
    }

    public function setLimitTime(\DateTimeInterface $limitTime): self
    {
        $this->limitTime = $limitTime;

        return $this;
    }

    public function isCreatedBy(User $author): bool
    {
        return $this->author->isEqualTo($author);
    }

    public function getSettings(): ?Settings
    {
        return $this->settings;
    }

    public function setSettings(?Settings $settings): self
    {
        $this->settings = $settings;

        return $this;
    }

    public function getTotalExamplesCount(): int
    {
        return $this->getSettings()->getExamplesCount() * $this->getTimesCount();
    }
}
