<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use  App\ApiPlatform\Filter\AttemptUserFilter;
use  App\DateTime\DateTime as DT;
use App\Entity\Attempt\Result;
use App\Serializer\Group;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AttemptRepository")
 * @ApiResource(
 *     normalizationContext={"groups"={Group::ATTEMPT}},
 *     itemOperations={
 *     "get"={"access_control"="is_granted('VIEW', object)"},
 *   "answer"={"access_control"="is_granted('SOLVE', object)", "path"="/attempts/{id}/answer.{_format}", "controller"="App\Controller\Api\AttemptController::answer", "method"="PUT"}
 *     },
 *     collectionOperations={
 * "get"
 *     }
 *     )
 * @ApiFilter(AttemptUserFilter::class)
 */
class Attempt
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Example", mappedBy="attempt", orphanRemoval=true)
     */
    private $examples;

    /**
     * @ORM\Column(type="datetime")
     */
    private $addTime;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Session", inversedBy="attempts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $session;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Task", inversedBy="attempts")
     */
    private $task;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Settings", inversedBy="attempts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $settings;

    /**
     * @ORM\OneToOne(targetEntity=Result::class, mappedBy="attempt", cascade={"persist", "remove"})
     */
    private $result;

    public function __construct()
    {
        $this->examples = new ArrayCollection();
        $this->addTime = new \DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Collection|Example[]
     */
    public function getExamples(): Collection
    {
        return $this->examples;
    }

    public function addExample(Example $example): self
    {
        if (!$this->examples->contains($example)) {
            $this->examples[] = $example;
            $example->setAttempt($this);
        }

        return $this;
    }

    public function removeExample(Example $example): self
    {
        if ($this->examples->contains($example)) {
            $this->examples->removeElement($example);
            // set the owning side to null (unless already changed)
            if ($example->getAttempt() === $this) {
                $example->setAttempt(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->getAddTime();
    }

    public function getAddTime(): ?DT
    {
        return DT::createFromDT($this->addTime);
    }

    public function getStartedAt(): DT
    {
        return $this->getAddTime();
    }

    public function setAddTime(\DateTimeInterface $addTime): self
    {
        $this->addTime = $addTime;

        return $this;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(? Session $session): self
    {
        $this->session = $session;

        return $this;
    }

    public function getExamplesCount()
    {
        return $this->getSettings()->getExamplesCount();
    }

    public function getLimitTime(): \DateTimeInterface
    {
        return DT::createFromTimestamp($this->getAddTime()->getTimestamp() + $this->getSettings()->getDuration()->getTimestamp());
    }

    public function getUser(): ?User
    {
        return $this->getSession()->getUser();
    }

    public function getTask(): ?Task
    {
        return $this->task;
    }

    public function setTask(?Task $task): self
    {
        $this->task = $task;

        return $this;
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

    public function getResult(): ?Result
    {
        return $this->result;
    }

    public function setResult(Result $result): self
    {
        $this->result = $result;

        // set the owning side of the relation if necessary
        if ($this !== $result->getAttempt()) {
            $result->setAttempt($this);
        }

        return $this;
    }

    public function isDone(): bool
    {
        return null !== $this->getResult() && 0 === $this->getResult()->getRemainedExamplesCount();
    }
}
