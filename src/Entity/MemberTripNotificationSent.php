<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'member_trip_notification_sent')]
#[ORM\UniqueConstraint(name: 'member_trip_notification_sent_unique', columns: ['member_id', 'trip_id'])]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class MemberTripNotificationSent
{
    #[ORM\JoinColumn(name: 'member_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private Member $member;

    #[ORM\JoinColumn(name: 'trip_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Trip::class)]
    private Trip $trip;

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private DateTime $created;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    public function __construct(Member $member, Trip $trip)
    {
        $this->member = $member;
        $this->trip = $trip;
    }

    public function getMember(): Member
    {
        return $this->member;
    }

    public function getTrip(): Trip
    {
        return $this->trip;
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
