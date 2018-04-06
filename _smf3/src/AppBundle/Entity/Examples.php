<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Examples
 *
 * @ORM\Table(name="examples", indexes={@ORM\Index(name="try_id", columns={"try_id"})})
 * @ORM\Entity
 */
class Examples extends \AppBundle\Model\Examples
{
    /**
     * @var \AppBundle\DateTime
     *
     * @ORM\Column(name="add_time", type="datetime", nullable=false)
     */
    protected $addTime;

    /**
     * @var float
     *
     * @ORM\Column(name="first", type="float", precision=10, scale=0, nullable=false)
     */
    protected $first;

    /**
     * @var integer
     *
     * @ORM\Column(name="sign", type="integer", nullable=false)
     */
    protected $sign;

    /**
     * @var float
     *
     * @ORM\Column(name="second", type="float", precision=10, scale=0, nullable=false)
     */
    protected $second;

    /**
     * @var float
     *
     * @ORM\Column(name="answer", type="float", precision=10, scale=0, nullable=true)
     */
    protected $answer;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_right", type="boolean", nullable=true)
     */
    protected $isRight;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \AppBundle\Entity\Tries
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Tries")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="try_id", referencedColumnName="id")
     * })
     */
    protected $try;


}

