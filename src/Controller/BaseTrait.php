<?php

namespace App\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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

    protected function denyAccess($message = null)
    {
        throw new AccessDeniedException($message);
    }

    protected function denyAccessIfGranted($attribute, $subject, $message)
    {
        if ($this->isGranted($attribute, $subject)) {
            $this->denyAccess($message);
        }
    }
}
