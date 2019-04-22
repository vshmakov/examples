<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Instantiator\Instantiator;
use  FOS\UserBundle\Mailer\Mailer as BaseMailer;

final class Mailer extends BaseMailer
{
    /** @var array */
    private $messages;

    public static function factory(): self
    {
        $instantiator = new Instantiator();

        return $instantiator->instantiate(self::class);
    }

    /**
     * @param string       $renderedTemplate
     * @param array|string $fromEmail
     * @param array|string $toEmail
     */
    protected function sendEmailMessage($renderedTemplate, $fromEmail, $toEmail)
    {
        $this->messages[] = $renderedTemplate;
    }

    public function getMessages(): Collection
    {
        return new ArrayCollection($this->messages);
    }
}
