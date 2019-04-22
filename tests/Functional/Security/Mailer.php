<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use  FOS\UserBundle\Mailer\Mailer as BaseMailer;

final class Mailer extends BaseMailer
{
    /** @var array */
    private $messages;

    protected function sendEmailMessage($renderedTemplate, $fromEmail, $toEmail)
    {
        $this->messages[] = $renderedTemplate;
    }

    public function getMessages(): Collection
    {
        return new ArrayCollection($this->messages);
    }
}
