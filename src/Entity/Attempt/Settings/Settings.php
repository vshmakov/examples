<?php

namespace App\Entity\Attempt\Settings;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="attempt_settings")
 */
class Settings extends BaseSettings
{
    protected $id;
}
