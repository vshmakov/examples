<?php
/**
 * Created by PhpStorm.
 * User: Вадим
 * Date: 08.03.2019
 * Time: 18:01.
 */

namespace App\Profile;

use App\Entity\Profile;

interface NormalizerInterface
{
    public function normalize(Profile $profile): void;
}
