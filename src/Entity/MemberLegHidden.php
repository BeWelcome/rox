<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'member_subtrip_hidden')]
#[ORM\UniqueConstraint(name: 'member_subtrip_hidden_unique', columns: ['member_id', 'subtrip_id'])]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class MemberLegHidden
{
    #[ORM\JoinColumn(name: 'member_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private Member $member;

    #[ORM\JoinColumn(name: 'subtrip_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Subtrip::class)]
    private Subtrip $leg;

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private DateTime $created;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    public function __construct(Member $member, Subtrip $subtrip)
    {
        $this->member = $member;
        $this->leg = $subtrip;
    }

    public function getMember(): Member
    {
        return $this->member;
    }

    public function getLeg(): Subtrip
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
