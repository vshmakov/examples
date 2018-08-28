<?php

namespace App\Entity;

use App\DT;

trait DTTrait
{
    use BaseTrait;

    public function __construct()
    {
        $this->initAddTime();
    }

    private function initAddTime($var = 'addTime')
    {
        $this->$var = new DT();
    }
}
