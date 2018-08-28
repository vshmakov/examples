<?php

namespace App\Controller;

trait BaseTrait
{
    protected function em()
    {
        return $this->getDoctrine()->getManager();
    }
}
