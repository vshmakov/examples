<?php

namespace App\Entity\Attempt\Settings;

use Doctrine\ORM\Mapping as ORM;

trait AdditionSettingsTrait
{
    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $minimumFirstAddend = 5;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $maximumFirstAddend = 10;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $minimumSecondAddend = 5;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $maximumSecondAddend = 10;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $minimumSum = 10;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $maximumSum = 20;

    public function getMinimumFirstAddend(): float
    {
        return $this->minimumFirstAddend;
    }

    public function setMinimumFirstAddend(float $minimumFirstAddend): void
    {
        $this->minimumFirstAddend = $minimumFirstAddend;
    }

    public function getMaximumFirstAddend(): float
    {
        return $this->maximumFirstAddend;
    }

    public function setMaximumFirstAddend(float $maximumFirstAddend): void
    {
        $this->maximumFirstAddend = $maximumFirstAddend;
    }

    public function getMinimumSecondAddend(): float
    {
        return $this->minimumSecondAddend;
    }

    public function setMinimumSecondAddend(float $minimumSecondAddend): void
    {
        $this->minimumSecondAddend = $minimumSecondAddend;
    }

    public function getMaximumSecondAddend(): float
    {
        return $this->maximumSecondAddend;
    }

    public function setMaximumSecondAddend(float $maximumSecondAddend): void
    {
        $this->maximumSecondAddend = $maximumSecondAddend;
    }

    public function getMinimumSum(): float
    {
        return $this->minimumSum;
    }

    public function setMinimumSum(float $minimumSum): void
    {
        $this->minimumSum = $minimumSum;
    }

    public function getMaximumSum(): float
    {
        return $this->maximumSum;
    }

    public function setMaximumSum(float $maximumSum): void
    {
        $this->maximumSum = $maximumSum;
    }
}
