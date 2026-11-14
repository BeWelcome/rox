<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'member_leg_notification_sent')]
#[ORM\UniqueConstraint(name: 'member_leg_notification_sent_unique', columns: ['member_id', 'leg_id'])]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class MemberLegNotificationSent
{
    #[ORM\JoinColumn(name: 'member_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private Member $member;

    #[ORM\JoinColumn(name: 'leg_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Leg::class)]
    private Leg $leg;

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private DateTime $created;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    public function __construct(Member $member, Leg $leg)
    {
        $this->member = $member;
        $this->leg = $leg;
    }

    public function getMember(): Member
    {
        return $this->member;
    }

    public function getLeg(): Leg
    {
        return $this->leg;
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
