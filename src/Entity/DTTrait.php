<?php

namespace App\Entity;

trait DTTrait
{
    use BaseTrait;

    public function __construct()
    {
        $this->initAddTime();
    }

    private function initAddTime($var = 'addTime')
    {
        $this->$var = new \DateTime();
    }
}
