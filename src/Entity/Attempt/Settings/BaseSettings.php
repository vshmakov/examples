<?php

namespace App\Entity\Attempt\Settings;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\PropertyAccess\PropertyAccess;

/** @ORM\MappedSuperclass */
abstract class BaseSettings
{
    use AdditionSettingsTrait;
}
