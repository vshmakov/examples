<?php

namespace App\Entity\Attempt\Settings;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class BaseSettings.
 *
 * @ORM\MappedSuperclass
 */
abstract class BaseSettings
{
    use AdditionSettingsTrait, SubtractionSettingsTrait, MultiplicationSettingsTrait;

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
}
