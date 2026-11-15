<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * A notification about an activity sent to a member near it (ActivityModel): each member gets one per activity.
 */
#[ORM\Table(name: 'member_activity_notification_sent')]
#[ORM\UniqueConstraint(name: 'member_activity_notification_sent_unique', columns: ['member_id', 'activity_id'])]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class MemberActivityNotificationSent
{
    #[ORM\JoinColumn(name: 'member_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private Member $member;

    #[ORM\JoinColumn(name: 'activity_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Activity::class)]
    private Activity $activity;

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private DateTime $created;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    public function __construct(Member $member, Activity $activity)
    {
        $this->member = $member;
        $this->activity = $activity;
    }

    public function getMember(): Member
    {
        return $this->member;
    }

    public function getActivity(): Activity
    {
        return $this->activity;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->created = new DateTime('now');
    }
}
