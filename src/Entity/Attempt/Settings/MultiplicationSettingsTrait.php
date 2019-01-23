<?php

namespace App\Entity\Attempt\Settings;

trait MultiplicationSettingsTrait
{
    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $minimumMultiplicands;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $maximumMultiplicands;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $minimumMultiplier;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $maximumMultiplier;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $minimumProduct;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $maximumProduct;

    public function getMinimumMultiplicands(): float
    {
        return $this->minimumMultiplicands;
    }

    public function setMinimumMultiplicands(float $minimumMultiplicands): void
    {
        $this->minimumMultiplicands = $minimumMultiplicands;
    }

    public function getMaximumMultiplicands(): float
    {
        return $this->maximumMultiplicands;
    }

    public function setMaximumMultiplicands(float $maximumMultiplicands): void
    {
        $this->maximumMultiplicands = $maximumMultiplicands;
    }

    public function getMinimumMultiplier(): float
    {
        return $this->minimumMultiplier;
    }

    public function setMinimumMultiplier(float $minimumMultiplier): void
    {
        $this->minimumMultiplier = $minimumMultiplier;
    }

    public function getMaximumMultiplier(): float
    {
        return $this->maximumMultiplier;
    }

    public function setMaximumMultiplier(float $maximumMultiplier): void
    {
        $this->maximumMultiplier = $maximumMultiplier;
    }

    public function getMinimumProduct(): float
    {
        return $this->minimumProduct;
    }

    public function setMinimumProduct(float $minimumProduct): void
    {
        $this->minimumProduct = $minimumProduct;
    }

    public function getMaximumProduct(): float
    {
        return $this->maximumProduct;
    }

    public function setMaximumProduct(float $maximumProduct): void
    {
        $this->maximumProduct = $maximumProduct;
    }
}
