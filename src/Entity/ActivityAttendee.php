<?php

/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use App\Repository\ActivityAttendeeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Do not check entities with PHPMD.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'activitiesattendees')]
#[ORM\Entity(repositoryClass: ActivityAttendeeRepository::class)]
class ActivityAttendee
{
    // The values the legacy activities pages wrote, and which are in the database.
    public const int ATTENDS_YES = 1;
    public const int ATTENDS_MAYBE = 2;
    public const int ATTENDS_NO = 3;
    // Set for everyone when an organizer cancels the activity; reactivating sets them back to yes.
    public const int ACTIVITY_CANCELLED = 4;

    /**
     * @var Activity
     */
    #[ORM\JoinColumn(name: 'activityId', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Activity::class, inversedBy: 'attendees')]
    private $activity;

    /**
     * @var Member
     */
    #[ORM\JoinColumn(name: 'attendeeId', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private $attendee;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'organizer', type: 'smallint', nullable: false)]
    private $organizer;

    /**
     * @var int
     */
    #[ORM\Column(name: 'status', type: 'smallint', nullable: false)]
    private $status;

    /**
     * @var string
     */
    #[ORM\Column(name: 'comment', type: 'string', length: 80, nullable: false)]
    private $comment;

    /**
     * @var int
     */
    // The primary key of activitiesattendees (AUTO_INCREMENT on production).
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * ActivityAttendee constructor.
     *
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
     * Set id.
     *
     * Legacy, non-identity column kept alongside the real (activity, attendee) composite primary key.
     *
     * @param int $id
     *
     * @return ActivityAttendee
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
     * Set attendee.
     *
     * @return ActivityAttendee
     */
    public function setAttendee(?Member $attendee = null)
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
     * @return ActivityAttendee
     */
    public function setActivity(?Activity $activity = null)
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
