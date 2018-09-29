<?php

namespace App\Controller;

trait BaseTrait
{
    protected function getEntityManager()
    {
        return $this->getDoctrine()->getManager();
    }
}
