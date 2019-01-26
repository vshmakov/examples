<?php

namespace App\Entity\Attempt\Settings;

use App\Object\ObjectAccessor;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class BaseSettings implements ArithmeticFunctionsSettingsInterface
{
    use AdditionSettingsTrait, SubtractionSettingsTrait, MultiplicationSettingsTrait, DivisionSettingsTrait;

    /**
     * @var int|null
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArithmeticProperties(): array
    {
        return ObjectAccessor::getValues($this, self::getArithmeticPropertyNames());
    }

    private static function getArithmeticPropertyNames(): array
    {
        return [
            'minimumFirstAddend',
            'maximumFirstAddend',
            'minimumSecondAddend',
            'maximumSecondAddend',
            'minimumSum',
            'maximumSum',

            'minimumMinuend',
            'maximumMinuend',
            'minimumSubtrahend',
            'maximumSubtrahend',
            'minimumDifference',
            'maximumDifference',

            'minimumMultiplicands',
            'maximumMultiplicands',
            'minimumMultiplier',
            'maximumMultiplier',
            'minimumProduct',
            'maximumProduct',

            'minimumDividend',
            'maximumDividend',
            'minimumDivisor',
            'maximumDivisor',
            'minimumQuotient',
            'maximumQuotient',
        ];
    }
}
