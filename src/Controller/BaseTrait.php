<?php

namespace App\Controller;

trait BaseTrait
{
    protected function em()
    {
        return $this->getEntityManager();
    }

    protected function getEntityManager()
    {
        return $this->getDoctrine()->getManager();
    }
}
