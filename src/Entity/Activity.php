<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * Activity.
 *
 * @ORM\Table(name="activities")
 * @ORM\Entity(repositoryClass="App\Repository\ActivityRepository")
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class Activity
{
    /**
     * @var Member
     *
     * @ORM\OneToOne(targetEntity="\App\Entity\Member")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="creator", referencedColumnName="id")
     * })
     */
    private $createdBy;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="dateTimeStart", type="datetime", nullable=false)
     */
    private $starts;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="dateTimeEnd", type="datetime", nullable=true)
     */
    private $ends;

    /**
     * @var Location
     *
     * @ORM\OneToOne(targetEntity="\App\Entity\Location")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="locationId", referencedColumnName="geonameid")
     * })
     */
    private $location;

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
     * @var int
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    private $status;

    /**
     * @var int
     *
     * @ORM\Column(name="public", type="smallint", nullable=true)
     */
    private $public;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ActivityAttendee", mappedBy="activity")
     */
    private $attendees;

    public function __construct()
    {
        $this->attendees = new ArrayCollection();
    }

    /**
     * Set createdBy.
     *
     * @param Member $createdBy
     *
     * @return Activity
     */
    public function setCreatedBy(Member $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy.
     *
     * @return Member
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set starts.
     *
     * @param DateTime $starts
     *
     * @return Activity
     */
    public function setStarts($starts)
    {
        $this->starts = $starts;

        return $this;
    }

    /**
     * Get starts.
     *
     * @return Carbon
     */
    public function getStarts()
    {
        return Carbon::instance($this->starts);
    }

    /**
     * Set ends.
     *
     * @param DateTime $ends
     *
     * @return Activity
     */
    public function setEnds($ends)
    {
        $this->ends = $ends;

        return $this;
    }

    /**
     * Get ends.
     *
     * @return Carbon
     */
    public function getEnds()
    {
        return Carbon::instance($this->ends);
    }

    /**
     * Set locationid.
     *
     * @param Location $location
     *
     * @return Activity
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location.
     *
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set address.
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
     * Get address.
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set title.
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
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description.
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
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set status.
     *
     * @param int $status
     *
     * @return Activity
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set public.
     *
     * @param int $public
     *
     * @return Activity
     */
    public function setPublic($public)
    {
        $this->public = $public;

        return $this;
    }

    /**
     * Get public.
     *
     * @return int
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * Get id.
     *
     * @return int
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

    /**
     * @return ArrayCollection
     */
    public function getAttendeesYes()
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('status', ActivityAttendee::ATTENDS_YES))
        ;

        $attendeesYes = $this->attendees->matching($criteria);

        return $attendeesYes;
    }

    /**
     * @return ArrayCollection
     */
    public function getAttendeesNo()
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('status', ActivityAttendee::ATTENDS_NO))
        ;

        $attendeesNo = $this->attendees->matching($criteria);

        return $attendeesNo;
    }

    /**
     * @return ArrayCollection
     */
    public function getAttendeesMaybe()
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('status', ActivityAttendee::ATTENDS_MAYBE))
        ;

        $attendeesMaybe = $this->attendees->matching($criteria);

        return $attendeesMaybe;
    }

    /**
     * @return ArrayCollection
     */
    public function getOrganizers()
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('organizer', '1'))
        ;

        $organizers = $this->attendees->matching($criteria);

        return $organizers;
    }

    /**
     * Add attendee.
     *
     * @return Activity
     */
    public function addAttendee(ActivityAttendee $attendee)
    {
        $this->attendees[] = $attendee;

        return $this;
    }

    /**
     * Remove attendee.
     */
    public function removeAttendee(ActivityAttendee $attendee)
    {
        $this->attendees->removeElement($attendee);
    }
}
