<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace AppBundle\Entity;

use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Activity
 *
 * @ORM\Table(name="activities")
 * @ORM\Entity
 */
class Activity
{
    /**
     * @var \AppBundle\Entity\Member
     *
     * @ORM\OneToOne(targetEntity="\AppBundle\Entity\Member")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="creator", referencedColumnName="id")
     * })
     */
    private $createdBy;

    /**
     * @var Carbon
     *
     * @ORM\Column(name="dateTimeStart", type="datetime", nullable=false)
     */
    private $datetimestart;

    /**
     * @var Carbon
     *
     * @ORM\Column(name="dateTimeEnd", type="datetime", nullable=true)
     */
    private $datetimeend;

    /**
     * @var integer
     *
     * @ORM\Column(name="locationId", type="integer", nullable=false)
     */
    private $locationid;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=320, nullable=true)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=80, nullable=false)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=16777215, nullable=true)
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    private $status;

    /**
     * @var integer
     *
     * @ORM\Column(name="public", type="smallint", nullable=true)
     */
    private $public;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ActivityAttendee", mappedBy="trip")
     */
    private $attendees;

    public function __construct()
    {
        $this->attendees = new ArrayCollection();
    }

    /**
     * Set createdBy
     *
     * @param \AppBundle\Entity\Member $createdBy
     *
     * @return Activity
     */
    public function setCreatedBy(\AppBundle\Entity\Member $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \AppBundle\Entity\Member
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set datetimestart
     *
     * @param \DateTime $datetimestart
     *
     * @return Activity
     */
    public function setDatetimestart($datetimestart)
    {
        $this->datetimestart = $datetimestart;

        return $this;
    }

    /**
     * Get datetimestart
     *
     * @return \DateTime
     */
    public function getDatetimestart()
    {
        return $this->datetimestart;
    }

    /**
     * Set datetimeend
     *
     * @param \DateTime $datetimeend
     *
     * @return Activity
     */
    public function setDatetimeend($datetimeend)
    {
        $this->datetimeend = $datetimeend;

        return $this;
    }

    /**
     * Get datetimeend
     *
     * @return \DateTime
     */
    public function getDatetimeend()
    {
        return $this->datetimeend;
    }

    /**
     * Set locationid
     *
     * @param integer $locationid
     *
     * @return Activity
     */
    public function setLocationid($locationid)
    {
        $this->locationid = $locationid;

        return $this;
    }

    /**
     * Get locationid
     *
     * @return integer
     */
    public function getLocationid()
    {
        return $this->locationid;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return Activity
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Activity
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Activity
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Activity
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set public
     *
     * @param integer $public
     *
     * @return Activity
     */
    public function setPublic($public)
    {
        $this->public = $public;

        return $this;
    }

    /**
     * Get public
     *
     * @return integer
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ArrayCollection
     */
    public function getAttendees()
    {
        return $this->attendees;
    }
}
