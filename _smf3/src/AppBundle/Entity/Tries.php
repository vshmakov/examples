<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tries
 *
 * @ORM\Table(name="tries")
 * @ORM\Entity
 */
class Tries extends \AppBundle\Model\Tries
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_time", type="datetime", nullable=false)
     */
    protected $startTime;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Sessions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="session_id", referencedColumnName="session_key")
     * })
     */
    protected $session;

    /**
     * @var string
     *
     * @ORM\Column(name="settings", type="object", nullable=false)
     */
    protected $settings;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;


}

