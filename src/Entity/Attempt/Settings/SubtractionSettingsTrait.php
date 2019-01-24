<?php

namespace App\Entity\Attempt\Settings;

use Doctrine\ORM\Mapping as ORM;

trait SubtractionSettingsTrait
{
    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $minimumMinuend = 10;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $maximumMinuend = 20;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $minimumSubtrahend = 5;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $maximumSubtrahend = 15;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $minimumDifference = 5;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $maximumDifference = 15;

    public function getMinimumMinuend(): float
    {
        return $this->minimumMinuend;
    }

    public function setMinimumMinuend(float $minimumMinuend): void
    {
        $this->minimumMinuend = $minimumMinuend;
    }

    public function getMaximumMinuend(): float
    {
        return $this->maximumMinuend;
    }

    public function setMaximumMinuend(float $maximumMinuend): void
    {
        $this->maximumMinuend = $maximumMinuend;
    }

    public function getMinimumSubtrahend(): float
    {
        return $this->minimumSubtrahend;
    }

    public function setMinimumSubtrahend(float $minimumSubtrahend): void
    {
        $this->minimumSubtrahend = $minimumSubtrahend;
    }

    public function getMaximumSubtrahend(): float
    {
        return $this->maximumSubtrahend;
    }

    public function setMaximumSubtrahend(float $maximumSubtrahend): void
    {
        $this->maximumSubtrahend = $maximumSubtrahend;
    }

    public function getMinimumDifference(): float
    {
        return $this->minimumDifference;
    }

    public function setMinimumDifference(float $minimumDifference): void
    {
        $this->minimumDifference = $minimumDifference;
    }

    public function getMaximumDifference(): float
    {
        return $this->maximumDifference;
    }

    public function setMaximumDifference(float $maximumDifference): void
    {
        $this->maximumDifference = $maximumDifference;
    }
}
