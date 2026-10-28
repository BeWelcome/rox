<?php

namespace App\Entity;

use App\Doctrine\GroupMembershipStatusType;
use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * A member's membership in a group (`group_membership`), filled from main's `membersgroups` by
 * Version20261029100000. The comment is the member's own text about why they are in the group.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'group_membership')]
#[ORM\UniqueConstraint(name: 'group_membership_unique', columns: ['member_id', 'group_id'])]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class GroupMembership
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Member::class, inversedBy: 'groupMemberships')]
    #[ORM\JoinColumn(name: 'member_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Member $member;

    #[ORM\ManyToOne(targetEntity: Group::class, inversedBy: 'groupMemberships')]
    #[ORM\JoinColumn(name: 'group_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Group $group = null;

    #[ORM\Column(name: 'status', type: 'group_membership_status', nullable: false)]
    private string $status = GroupMembershipStatusType::APPLIED_FOR_MEMBERSHIP;

    #[ORM\Column(name: 'comment', type: 'text', nullable: true)]
    private ?string $comment = null;

    /** The member accepts mass mailings sent to the group's members. */
    #[ORM\Column(name: 'accept_mass_mail', type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $acceptMassMail = false;

    /** The member gets notified about new posts in the group's forum. */
    #[ORM\Column(name: 'notifications_enabled', type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $notificationsEnabled = true;

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private DateTime $created;

    #[ORM\Column(name: 'updated', type: 'datetime', nullable: false)]
    private DateTime $updated;

    public function getId(): int
    {
        return $this->id;
    }

    public function setMember(Member $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function getMember(): Member
    {
        return $this->member;
    }

    public function setGroup(?Group $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function getGroup(): ?Group
    {
        return $this->group;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setAcceptMassMail(bool $acceptMassMail): self
    {
        $this->acceptMassMail = $acceptMassMail;

        return $this;
    }

    public function isAcceptMassMail(): bool
    {
        return $this->acceptMassMail;
    }

    public function setNotificationsEnabled(bool $notificationsEnabled): self
    {
        $this->notificationsEnabled = $notificationsEnabled;

        return $this;
    }

    public function isNotificationsEnabled(): bool
    {
        return $this->notificationsEnabled;
    }

    public function setCreated(DateTime $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getCreated(): Carbon
    {
        return Carbon::instance($this->created);
    }

    public function setUpdated(DateTime $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function getUpdated(): Carbon
    {
        return Carbon::instance($this->updated);
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->created ??= new DateTime();
        $this->updated = new DateTime();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updated = new DateTime();
    }
}
