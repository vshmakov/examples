<?php

namespace App\Entity\Attempt\Settings;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\MappedSuperclass */
abstract class BaseSettings
{
    use AdditionSettingsTrait;
}
