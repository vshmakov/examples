<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Profiles
 *
 * @ORM\Table(name="profiles")
 * @ORM\Entity
 */
class Profiles extends \AppBundle\Model\Profiles
{

    /**
     * @var \AppBundle\Entity\Users
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    protected $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_time", type="datetime", nullable=false)
     */
    public $createTime;

    /**
     * @var integer
     *
     * @ORM\Column(name="try_duration", type="integer", nullable=false)
     */
    public $tryDuration;

    /**
     * @var integer
     *
     * @ORM\Column(name="examples_count", type="integer", nullable=false)
     */
    public $examplesCount;

    /**
     * @var integer
     *
     * @ORM\Column(name="add_min", type="integer", nullable=true)
     */
    public $addMin;

    /**
     * @var integer
     *
     * @ORM\Column(name="add_max", type="integer", nullable=true)
     */
    public $addMax;

    /**
     * @var integer
     *
     * @ORM\Column(name="sub_min", type="integer", nullable=true)
     */
    public $subMin;

    /**
     * @var integer
     *
     * @ORM\Column(name="sub_max", type="integer", nullable=true)
     */
    public $subMax;

    /**
     * @var integer
     *
     * @ORM\Column(name="min_sub", type="integer", nullable=true)
     */
    public $minSub;

    /**
     * @var integer
     *
     * @ORM\Column(name="mult_min", type="integer", nullable=true)
     */
    public $multMin;

    /**
     * @var integer
     *
     * @ORM\Column(name="mult_max", type="integer", nullable=true)
     */
    public $multMax;

    /**
     * @var integer
     *
     * @ORM\Column(name="div_min", type="integer", nullable=true)
     */
    public $divMin;

    /**
     * @var integer
     *
     * @ORM\Column(name="div_max", type="integer", nullable=true)
     */
    public $divMax;

    /**
     * @var integer
     *
     * @ORM\Column(name="min_div", type="integer", nullable=true)
     */
    public $minDiv;

    /**
     * @var integer
     *
     * @ORM\Column(name="add_perc", type="integer", nullable=true)
     */
    public $addPerc;

    /**
     * @var integer
     *
     * @ORM\Column(name="sub_perc", type="integer", nullable=true)
     */
    public $subPerc;

    /**
     * @var integer
     *
     * @ORM\Column(name="mult_perc", type="integer", nullable=true)
     */
    public $multPerc;

    /**
     * @var integer
     *
     * @ORM\Column(name="div_perc", type="integer", nullable=true)
     */
    public $divPerc;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=63, nullable=false)
     */
    public $description;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_public", type="boolean", nullable=false)
     */
    public $isPublic;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;


}

