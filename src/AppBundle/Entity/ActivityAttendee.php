<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ActivityAttendee
 *
 * @ORM\Table(name="activitiesattendees")
 * @ORM\Entity
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class ActivityAttendee
{
    /**
     * @var \AppBundle\Entity\Activity
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\AppBundle\Entity\Activity")
     * @ORM\JoinColumn(name="activityId", referencedColumnName="id")
     */
    private $activity;

    /**
     * @var \AppBundle\Entity\Member
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\AppBundle\Entity\Member")
     * @ORM\JoinColumn(name="attendeeId", referencedColumnName="id")
     */
    private $attendee;

    /**
     * @var boolean
     *
     * @ORM\Column(name="organizer", type="smallint", nullable=false)
     */
    private $organizer;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=80, nullable=false)
     */
    private $comment;

    /**
     * ActivityAttendee constructor.
     *
     * @param Activity $activity
     * @param Member $attendee
     * @param $status
     * @param $comment
     * @param bool $organizer
     */
    public function __construct(Activity $activity, Member $attendee, $status, $comment, $isOrganizer = false)
    {
        $this->setActivity($activity);
        $this->setAttendee($attendee);
        $this->setStatus($status);
        $this->setComment($comment);
        $this->setOrganizer($isOrganizer);
    }

    /**
     * Set attendee
     *
     * @param \AppBundle\Entity\Member $attendee
     *
     * @return ActivityAttendee
     */
    public function setAttendee(\AppBundle\Entity\Member $attendee = null)
    {
        $this->attendee = $attendee;

        return $this;
    }

    /**
     * Get attendee
     *
     * @return \AppBundle\Entity\Member
     */
    public function getAttendee()
    {
        return $this->attendee;
    }

    /**
     * Set activity
     *
     * @param \AppBundle\Entity\Activity $activity
     *
     * @return ActivityAttendee
     */
    public function setActivity(\AppBundle\Entity\Activity $activity = null)
    {
        $this->activity = $activity;

        return $this;
    }

    /**
     * Get activity
     *
     * @return \AppBundle\Entity\Activity
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * Set organizer
     *
     * @param integer $organizer
     *
     * @return ActivityAttendee
     */
    public function setOrganizer($organizer)
    {
        $this->organizer = $organizer;

        return $this;
    }

    /**
     * Get organizer
     *
     * @return integer
     */
    public function getOrganizer()
    {
        return $this->organizer;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return ActivityAttendee
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
     * Set comment
     *
     * @param string $comment
     *
     * @return ActivityAttendee
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }
}
