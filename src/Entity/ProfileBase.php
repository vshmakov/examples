<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\PropertyAccess\PropertyAccess;

/** @ORM\MappedSuperclass */
abstract class ProfileBase
{
    use BaseTrait;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $addTime;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $duration = 180;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $examplesCount = 5;

    /**
     * @ORM\Column(type="integer")
     */
    protected $addFMin = 0;

    /**
     * @ORM\Column(type="integer")
     */
    protected $addFMax = 3;

    /**
     * @ORM\Column(type="integer")
     */
    protected $addSMin = 0;

    /**
     * @ORM\Column(type="integer")
     */
    protected $addSMax = 3;

    /**
     * @ORM\Column(type="integer")
     */
    protected $addMin = -1;

    /**
     * @ORM\Column(type="integer")
     */
    protected $addMax = -1;

    /**
     * @ORM\Column(type="integer")
     */
    protected $subFMin = 0;

    /**
     * @ORM\Column(type="integer")
     */
    protected $subFMax = 5;

    /**
     * @ORM\Column(type="integer")
     */
    protected $subSMin = 0;

    /**
     * @ORM\Column(type="integer")
     */
    protected $subSMax = 5;

    /**
     * @ORM\Column(type="integer")
     */
    protected $subMin = 0;

    /**
     * @ORM\Column(type="integer")
     */
    protected $subMax = 1000;

    /**
     * @ORM\Column(type="integer")
     */
    protected $multFMin = 0;

    /**
     * @ORM\Column(type="integer")
     */
    protected $multFMax = 3;

    /**
     * @ORM\Column(type="integer")
     */
    protected $multSMin = 0;

    /**
     * @ORM\Column(type="integer")
     */
    protected $multSMax = 3;

    /**
     * @ORM\Column(type="integer")
     */
    protected $multMin = -1;

    /**
     * @ORM\Column(type="integer")
     */
    protected $multMax = -1;

    /**
     * @ORM\Column(type="integer")
     */
    protected $divFMin = 0;

    /**
     * @ORM\Column(type="integer")
     */
    protected $divFMax = 6;

    /**
     * @ORM\Column(type="integer")
     */
    protected $divSMin = 1;

    /**
     * @ORM\Column(type="integer")
     */
    protected $divSMax = 6;

    /**
     * @ORM\Column(type="integer")
     */
    protected $divMin = -1;

    /**
     * @ORM\Column(type="integer")
     */
    protected $divMax = -1;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $addPerc = 25;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $subPerc = 25;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $multPerc = 25;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $divPerc = 25;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $description;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isDemanding = true;

    public function __construct()
    {
        $this->addTime = new \DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAddTime() : ? \DateTimeInterface
    {
        return $this->dt($this->addTime);
    }

    public function setAddTime(\DateTimeInterface $addTime) : self
    {
        $this->addTime = $addTime;

        return $this;
    }

    public function getDuration() : ? int
    {
        return $this->duration;
    }

    public function setDuration(int $duration) : self
    {
        $this->duration = btwVal(30, HOUR - 1, $duration);

        return $this;
    }

    public function getExamplesCount() : ? int
    {
        return $this->examplesCount;
    }

    public function setExamplesCount(int $examplesCount) : self
    {
        $this->examplesCount = btwVal(3, 150, $examplesCount);

        return $this;
    }

    public function getAddPerc() : ? int
    {
        return $this->addPerc;
    }

    public function setAddPerc(int $addPerc) : self
    {
        $this->addPerc = $addPerc;

        return $this;
    }

    public function getSubPerc() : ? int
    {
        return $this->subPerc;
    }

    public function setSubPerc(int $subPerc) : self
    {
        $this->subPerc = $subPerc;

        return $this;
    }

    public function getMultPerc() : ? int
    {
        return $this->multPerc;
    }

    public function setMultPerc(int $multPerc) : self
    {
        $this->multPerc = $multPerc;

        return $this;
    }

    public function getDivPerc() : ? int
    {
        return $this->divPerc;
    }

    public function setDivPerc(int $divPerc) : self
    {
        $this->divPerc = $divPerc;

        return $this;
    }

    public function getMinutes() : int
    {
        return $this->duration / MIN;
    }

    public function setMinutes(int $min)
    {
        $min = minVal(0, $min);
        $this->setDuration($min * MIN + $this->getSeconds());

        return $this;
    }

    public function getSeconds() : int
    {
        return $this->duration % MIN;
    }

    public function setSeconds(int $sec)
    {
        $sec = btwVal(0, 59, $sec);
        $this->setDuration($this->getMinutes() * MIN + $sec);

        return $this;
    }

    public function getDescription() : ? string
    {
        return $this->description;
    }

    public function setDescription(string $description) : self
    {
        $this->description = $description;

        return $this;
    }

    public function __toString()
    {
        return $this->getDescription() . ' - ' . $this->getAuthor()->getUsername();
    }

    public function getSettings()
    {
        $propertyAccessor = self::createPropertyAccessor()();

        return array_reduce(
            self::getSettingsFields(),
            function ($settings, $property) use ($propertyAccessor) : array {
                $settings[$property] = $propertyAccessor->getValue($this, $property);

                return $settings;
            },
            []
        );
    }

    public function isDemanding() : ? bool
    {
        return $this->isDemanding;
    }

    public function setIsDemanding(bool $isDemanding) : self
    {
        $this->isDemanding = $isDemanding;

        return $this;
    }

    public function __clone()
    {
        $this->id = null;
        $this->addTime = new \DateTime();
    }

    public function __get($property)
    {
        return $this->$property;
    }

    public function __set($property, $value)
    {
        $this->$property = $value;

        return $this;
    }

    public static function copySettings(self $source, self $target) : self
    {
        $propertyAccessor = self::createPropertyAccessor();

        return array_reduce(
            self::getSettingsFields(),
            function ($target, $property) use ($source, $propertyAccessor) : self {
                                $propertyAccessor->setValue(
                    $target,
                    $property,
                    $propertyAccessor->getValue($source, $property)
                );

                return $target;
            },
            $target
        );
    }

    protected static function createPropertyAccessor()
    {
        return PropertyAccess::createPropertyAccessorBuilder()
            ->enableExceptionOnInvalidIndex()
            ->getPropertyAccessor();
    }

    private static function getSettingsFields() : array
    {
        return arr('duration examplesCount addFMin addFMax addSMin addSMax addMin addMax subFMin subFMax subSMin subSMax subMin subMax multFMin multFMax multSMin multSMax multMin multMax divFMin divFMax divSMin divSMax divMin divMax addPerc subPerc multPerc divPerc description');
    }
}
