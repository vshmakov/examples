<?php

namespace App\Controller\Traits;

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

    protected function denyAccess(...$parameters)
    {
        throw $this->createAccessDeniedException(...$parameters);
    }
}
