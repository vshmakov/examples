<?php

namespace App\Controller;

trait BaseTrait
{
    protected function getEntityManager()
    {
        return $this->getDoctrine()->getManager();
    }

    protected function missResponseEvent()
    {
        $this->addFlash('missResponseEvent', true);
    }
}
