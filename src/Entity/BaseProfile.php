<?php

namespace App\Entity;

use App\DateTime\DateTime as DT;
use App\Serializer\Group;
use App\Validator as AppAssert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\MappedSuperclass */
abstract class BaseProfile
{
    /**
     * @ORM\Column(type="datetime")
     */
    protected $addTime;

    /**
     * @ORM\Column(type="smallint")
     * @Groups({Group::SETTINGS, Group::ATTEMPT})
     */
    protected $duration = 180;

    /**
     * @ORM\Column(type="smallint")
     * @AppAssert\NumberBetween(minimum=3, maximum=100)
     * @Groups({Group::SETTINGS, Group::ATTEMPT})
     */
    protected $examplesCount = 5;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @AppAssert\NumberBetween(minimum=0, maximum=100000)
     * @Groups({Group::SETTINGS, Group::MATHEMATICAL_SETTINGS})
     */
    protected $addFMin = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @AppAssert\NumberBetween(minimum=0, maximum=100000)
     * @Groups({Group::SETTINGS, Group::MATHEMATICAL_SETTINGS})
     * @Assert\GreaterThanOrEqual(propertyPath="addFMin", message="Maximum value must be greater or equal to minimum value.")
     */
    protected $addFMax = 3;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @AppAssert\NumberBetween(minimum=0, maximum=100000)
     * @Groups({Group::SETTINGS, Group::MATHEMATICAL_SETTINGS})
     */
    protected $addSMin = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @AppAssert\NumberBetween(minimum=0, maximum=100000)
     * @Assert\GreaterThanOrEqual(propertyPath="addSMin", message="Maximum value must be greater or equal to minimum value.")
     * @Groups({Group::SETTINGS, Group::MATHEMATICAL_SETTINGS})
     */
    protected $addSMax = 3;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Groups({Group::SETTINGS, Group::MATHEMATICAL_SETTINGS})
     */
    protected $addMin = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Groups({Group::SETTINGS, Group::MATHEMATICAL_SETTINGS})
     */
    protected $addMax = 6;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @AppAssert\NumberBetween(minimum=0, maximum=100000)
     * @Groups({Group::SETTINGS, Group::MATHEMATICAL_SETTINGS})
     */
    protected $subFMin = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @AppAssert\NumberBetween(minimum=0, maximum=100000)
     * @Assert\GreaterThanOrEqual(propertyPath="subFMin", message="Maximum value must be greater or equal to minimum value.")
     * @Groups({Group::SETTINGS, Group::MATHEMATICAL_SETTINGS})
     */
    protected $subFMax = 5;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @AppAssert\NumberBetween(minimum=0, maximum=100000)
     * @Groups({Group::SETTINGS, Group::MATHEMATICAL_SETTINGS})
     */
    protected $subSMin = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @AppAssert\NumberBetween(minimum=0, maximum=100000)
     * @Assert\GreaterThanOrEqual(propertyPath="subSMin", message="Maximum value must be greater or equal to minimum value.")
     * @Groups({Group::SETTINGS, Group::MATHEMATICAL_SETTINGS})
     */
    protected $subSMax = 5;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Groups({Group::SETTINGS, Group::MATHEMATICAL_SETTINGS})
     */
    protected $subMin = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Groups({Group::SETTINGS, Group::MATHEMATICAL_SETTINGS})
     */
    protected $subMax = 100;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @AppAssert\NumberBetween(minimum=0, maximum=100000)
     * @Groups({Group::SETTINGS, Group::MATHEMATICAL_SETTINGS})
     */
    protected $multFMin = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @AppAssert\NumberBetween(minimum=0, maximum=100000)
     * @Assert\GreaterThanOrEqual(propertyPath="multFMin", message="Maximum value must be greater or equal to minimum value.")
     * @Groups({Group::SETTINGS, Group::MATHEMATICAL_SETTINGS})
     */
    protected $multFMax = 3;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @AppAssert\NumberBetween(minimum=0, maximum=100000)
     * @Groups({Group::SETTINGS, Group::MATHEMATICAL_SETTINGS})
     */
    protected $multSMin = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @AppAssert\NumberBetween(minimum=0, maximum=100000)
     * @Assert\GreaterThanOrEqual(propertyPath="multSMin", message="Maximum value must be greater or equal to minimum value.")
     * @Groups({Group::SETTINGS, Group::MATHEMATICAL_SETTINGS})
     */
    protected $multSMax = 3;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Groups({Group::SETTINGS, Group::MATHEMATICAL_SETTINGS})
     */
    protected $multMin = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Groups({Group::SETTINGS, Group::MATHEMATICAL_SETTINGS})
     */
    protected $multMax = 100;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @AppAssert\NumberBetween(minimum=0, maximum=100000)
     * @Groups({Group::SETTINGS, Group::MATHEMATICAL_SETTINGS})
     */
    protected $divFMin = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @AppAssert\NumberBetween(minimum=0, maximum=100000)
     * @Assert\GreaterThanOrEqual(propertyPath="divFMin", message="Maximum value must be greater or equal to minimum value.")
     * @Groups({Group::SETTINGS, Group::MATHEMATICAL_SETTINGS})
     */
    protected $divFMax = 6;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @AppAssert\NumberBetween(minimum=1, maximum=100000)
     * @Groups({Group::SETTINGS, Group::MATHEMATICAL_SETTINGS})
     */
    protected $divSMin = 1;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @AppAssert\NumberBetween(minimum=1, maximum=100000)
     * @Assert\GreaterThanOrEqual(propertyPath="divSMin", message="Maximum value must be greater or equal to minimum value.")
     * @Groups({Group::SETTINGS, Group::MATHEMATICAL_SETTINGS})
     */
    protected $divSMax = 6;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Groups({Group::SETTINGS, Group::MATHEMATICAL_SETTINGS})
     */
    protected $divMin = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Groups({Group::SETTINGS, Group::MATHEMATICAL_SETTINGS})
     */
    protected $divMax = 100;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint")
     * @Assert\GreaterThanOrEqual(0)
     * @Groups({Group::SETTINGS})
     */
    protected $addPerc = 25;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint")
     * @Assert\GreaterThanOrEqual(0)
     * @Groups({Group::SETTINGS})
     */
    protected $subPerc = 25;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint")
     * @Assert\GreaterThanOrEqual(0)
     * @Groups({Group::SETTINGS})
     */
    protected $multPerc = 25;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint")
     * @Assert\GreaterThanOrEqual(0)
     * @Groups({Group::SETTINGS})
     */
    protected $divPerc = 25;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(
     *     min="3",
     *     minMessage="Description must contains more than {{ limit }} characters.",
     *     max="35",
     *     maxMessage="Description must contains less than {{ limit }} characters."
     * )
     * @Groups({Group::ATTEMPT, Group::SETTINGS, Group::EXAMPLE})
     */
    protected $description;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $isDemanding = true;

    public function __construct()
    {
        $this->addTime = new \DateTime();
    }

    /**
     * @Groups({Group::ATTEMPT})
     */
    public function getId()
    {
        return $this->id;
    }

    public function getAddTime(): ?\DateTimeInterface
    {
        return DT::createFromDT($this->addTime);
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->getAddTime();
    }

    public function setAddTime(\DateTimeInterface $addTime): self
    {
        $this->addTime = $addTime;

        return $this;
    }

    /**
     * @AppAssert\DateTimeBetween(minimum="PT30S", maximum="PT30M")
     */
    public function getDuration(): ?\DateTimeInterface
    {
        return DT::createFromTimestamp($this->duration);
    }

    public function setDuration(\DateTimeInterface $duration): void
    {
        $this->duration = $duration->getTimestamp();
    }

    public function getExamplesCount(): ?int
    {
        return (int) $this->examplesCount;
    }

    public function setExamplesCount(int $examplesCount): void
    {
        $this->examplesCount = $examplesCount;
    }

    public function getAddFMin(): int
    {
        return $this->addFMin;
    }

    public function setAddFMin(int $addFMin): self
    {
        $this->addFMin = $addFMin;

        return $this;
    }

    public function getAddFMax(): int
    {
        return $this->addFMax;
    }

    public function setAddFMax(int $addFMax): self
    {
        $this->addFMax = $addFMax;

        return $this;
    }

    public function getAddSMin(): int
    {
        return $this->addSMin;
    }

    public function setAddSMin(int $addSMin): self
    {
        $this->addSMin = $addSMin;

        return $this;
    }

    public function getAddSMax(): int
    {
        return $this->addSMax;
    }

    public function setAddSMax(int $addSMax): self
    {
        $this->addSMax = $addSMax;

        return $this;
    }

    public function getAddMin(): int
    {
        return $this->addMin;
    }

    public function setAddMin(int $addMin): self
    {
        $this->addMin = $addMin;

        return $this;
    }

    public function getAddMax(): int
    {
        return $this->addMax;
    }

    public function setAddMax(int $addMax): self
    {
        $this->addMax = $addMax;

        return $this;
    }

    public function getSubFMin(): int
    {
        return $this->subFMin;
    }

    public function setSubFMin(int $subFMin): self
    {
        $this->subFMin = $subFMin;

        return $this;
    }

    public function getSubFMax(): int
    {
        return $this->subFMax;
    }

    public function setSubFMax(int $subFMax): self
    {
        $this->subFMax = $subFMax;

        return $this;
    }

    public function getSubSMin(): int
    {
        return $this->subSMin;
    }

    public function setSubSMin(int $subSMin): self
    {
        $this->subSMin = $subSMin;

        return $this;
    }

    public function getSubSMax(): int
    {
        return $this->subSMax;
    }

    public function setSubSMax(int $subSMax): self
    {
        $this->subSMax = $subSMax;

        return $this;
    }

    public function getSubMin(): int
    {
        return $this->subMin;
    }

    public function setSubMin(int $subMin): self
    {
        $this->subMin = $subMin;

        return $this;
    }

    public function getSubMax(): int
    {
        return $this->subMax;
    }

    public function setSubMax(int $subMax): self
    {
        $this->subMax = $subMax;

        return $this;
    }

    public function getMultFMin(): int
    {
        return $this->multFMin;
    }

    public function setMultFMin(int $multFMin): self
    {
        $this->multFMin = $multFMin;

        return $this;
    }

    public function getMultFMax(): int
    {
        return $this->multFMax;
    }

    public function setMultFMax(int $multFMax): self
    {
        $this->multFMax = $multFMax;

        return $this;
    }

    public function getMultSMin(): int
    {
        return $this->multSMin;
    }

    public function setMultSMin(int $multSMin): self
    {
        $this->multSMin = $multSMin;

        return $this;
    }

    public function getMultSMax(): int
    {
        return $this->multSMax;
    }

    public function setMultSMax(int $multSMax): self
    {
        $this->multSMax = $multSMax;

        return $this;
    }

    public function getMultMin(): int
    {
        return $this->multMin;
    }

    public function setMultMin(int $multMin): self
    {
        $this->multMin = $multMin;

        return $this;
    }

    public function getMultMax(): int
    {
        return $this->multMax;
    }

    public function setMultMax(int $multMax): self
    {
        $this->multMax = $multMax;

        return $this;
    }

    public function getDivFMin(): int
    {
        return $this->divFMin;
    }

    public function setDivFMin(int $divFMin): self
    {
        $this->divFMin = $divFMin;

        return $this;
    }

    public function getDivFMax(): int
    {
        return $this->divFMax;
    }

    public function setDivFMax(int $divFMax): self
    {
        $this->divFMax = $divFMax;

        return $this;
    }

    public function getDivSMin(): int
    {
        return $this->divSMin;
    }

    public function setDivSMin(int $divSMin): self
    {
        $this->divSMin = $divSMin;

        return $this;
    }

    public function getDivSMax(): int
    {
        return $this->divSMax;
    }

    public function setDivSMax(int $divSMax): self
    {
        $this->divSMax = $divSMax;

        return $this;
    }

    public function getDivMin(): int
    {
        return $this->divMin;
    }

    public function setDivMin(int $divMin): self
    {
        $this->divMin = $divMin;

        return $this;
    }

    public function getDivMax(): int
    {
        return $this->divMax;
    }

    public function setDivMax(int $divMax): self
    {
        $this->divMax = $divMax;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function __toString()
    {
        return $this->getDescription();
    }

    public function isDemanding(): bool
    {
        return $this->isDemanding;
    }

    public function setIsDemanding(bool $isDemanding): self
    {
        $this->isDemanding = $isDemanding;

        return $this;
    }

    public function __clone()
    {
        $this->id = null;
        $this->addTime = new \DateTime();
    }
}
