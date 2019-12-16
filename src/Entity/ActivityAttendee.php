<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ActivityAttendee.
 *
 * @ORM\Table(name="activitiesattendees")
 * @ORM\Entity(repositoryClass="App\Repository\ActivityAttendeeRepository")
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class ActivityAttendee
{
    public const ATTENDS_NO = 0;
    public const ATTENDS_YES = 1;
    public const ATTENDS_MAYBE = 2;

    /**
     * @var Activity
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\App\Entity\Activity", inversedBy="attendees")
     * @ORM\JoinColumn(name="activityId", referencedColumnName="id")
     */
    private $activity;

    /**
     * @var Member
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\App\Entity\Member")
     * @ORM\JoinColumn(name="attendeeId", referencedColumnName="id")
     */
    private $attendee;

    /**
     * @var bool
     *
     * @ORM\Column(name="organizer", type="smallint", nullable=false)
     */
    private $organizer;

    /**
     * @var int
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
     * @param Member   $attendee
     * @param $status
     * @param $comment
     * @param bool $isOrganizer
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
     * Set attendee.
     *
     * @param Member $attendee
     *
     * @return ActivityAttendee
     */
    public function setAttendee(Member $attendee = null)
    {
        $this->attendee = $attendee;

        return $this;
    }

    /**
     * Get attendee.
     *
     * @return Member
     */
    public function getAttendee()
    {
        return $this->attendee;
    }

    /**
     * Set activity.
     *
     * @param Activity $activity
     *
     * @return ActivityAttendee
     */
    public function setActivity(Activity $activity = null)
    {
        $this->activity = $activity;

        return $this;
    }

    /**
     * Get activity.
     *
     * @return Activity
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * Set organizer.
     *
     * @param int $organizer
     *
     * @return ActivityAttendee
     */
    public function setOrganizer($organizer)
    {
        $this->organizer = $organizer;

        return $this;
    }

    /**
     * Get organizer.
     *
     * @return int
     */
    public function getOrganizer()
    {
        return $this->organizer;
    }

    /**
     * Set status.
     *
     * @param int $status
     *
     * @return ActivityAttendee
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
     * Set comment.
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
     * Get comment.
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }
}
