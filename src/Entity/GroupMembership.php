<?php

/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use App\Doctrine\GroupMembershipStatusType;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Mapping as ORM;

/**
 * Group Membership.
 *
 * @SuppressWarnings("PHPMD")
 * Auto generated class do not check mess
 */
#[ORM\Table(name: 'membersgroups')]
#[ORM\Index(name: 'membersgroups_groups', columns: ['IdGroup'])]
#[ORM\Index(name: 'membersgroups_members', columns: ['IdMember'])]
#[ORM\UniqueConstraint(name: 'UniqueIdMemberIdGroup', columns: ['IdMember', 'IdGroup'])]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class GroupMembership
{
    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'updated', type: 'datetime', nullable: false)]
    private $updated;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private $created;

    /**
     * @var int
     */
    #[ORM\Column(name: 'comment', type: 'integer', nullable: false)]
    private $comment;

    /**
     * @var MemberTranslation[]
     */
    private $comments;

    /**
     * @var Member
     */
    #[ORM\JoinColumn(name: 'IdMember', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \Member::class, inversedBy: 'groupMemberships')]
    private $member;

    /**
     * @var Group
     */
    #[ORM\JoinColumn(name: 'IdGroup', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \Group::class, inversedBy: 'groupMemberships')]
    private $group;

    /**
     * @var string
     */
    #[ORM\Column(name: 'Status', type: 'group_membership_status', nullable: false)]
    private $status = GroupMembershipStatusType::APPLIED_FOR_MEMBERSHIP;

    /**
     * @var string
     */
    #[ORM\Column(name: 'IacceptMassMailFromThisGroup', type: 'string', nullable: false)]
    private $mailNotifications = 'no';

    /**
     * @var string
     */
    #[ORM\Column(name: 'CanSendGroupMessage', type: 'string', nullable: false)]
    private $cansendgroupmessage = 'yes';

    /**
     * @var bool
     */
    #[ORM\Column(name: 'notificationsEnabled', type: 'boolean', nullable: false)]
    private $notificationsenabled = '1';

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    /**
     * Set updated.
     *
     * @param \DateTime $updated
     *
     * @return GroupMembership
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return GroupMembership
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return Carbon
     */
    public function getCreated()
    {
        return Carbon::instance($this->created);
    }

    /**
     * Set member.
     *
     * @param Member $member
     *
     * @return GroupMembership
     */
    public function setMember($member)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get member.
     *
     * @return Member
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Set group.
     *
     * @param Group $group
     *
     * @return GroupMembership
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group.
     *
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return GroupMembership
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set accept mail notifications.
     *
     * @param string $mailNotifications
     *
     * @return GroupMembership
     */
    public function setAcceptMailNotifications($mailNotifications)
    {
        $this->mailNotifications = $mailNotifications;

        return $this;
    }

    /**
     * Get accept mail notifications.
     *
     * @return string
     */
    public function getAcceptMailNotifications()
    {
        return $this->mailNotifications;
    }

    /**
     * Set cansendgroupmessage.
     *
     * @param string $cansendgroupmessage
     *
     * @return GroupMembership
     */
    public function setCansendgroupmessage($cansendgroupmessage)
    {
        $this->cansendgroupmessage = $cansendgroupmessage;

        return $this;
    }

    /**
     * Get cansendgroupmessage.
     *
     * @return string
     */
    public function getCansendgroupmessage()
    {
        return $this->cansendgroupmessage;
    }

    /**
     * Set notificationsenabled.
     *
     * @param bool $notificationsenabled
     *
     * @return GroupMembership
     */
    public function setNotificationsenabled($notificationsenabled)
    {
        $this->notificationsenabled = $notificationsenabled;

        return $this;
    }

    /**
     * Get notificationsenabled.
     *
     * @return bool
     */
    public function getNotificationsenabled()
    {
        return $this->notificationsenabled;
    }

    /**
     * Add a comment for the membership.
     *
     * @return GroupMembership
     */
    public function addComment(MemberTranslation $comment)
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $this->setComment($comment->getId());
        }

        return $this;
    }

    /**
     * Remove a comment from the membership.
     *
     * @return GroupMembership
     */
    public function removeComment(MemberTranslation $comment)
    {
        if ($this->comments->contains($comment)) {
            $this->comments->remove($comment);
        }

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
     * Triggered after load from database.
     */
    #[ORM\PostLoad]
    public function onPostLoad(PostLoadEventArgs $eventArgs): void
    {
        $memberTranslationRepository = $eventArgs->getObjectManager()->getRepository(MemberTranslation::class);
        $this->comments = $memberTranslationRepository->findBy(['translation' => $this->comment]);
    }

    /**
     * Triggered on insert.
     */
    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->created = new \DateTime('now');
        $this->updated = $this->created;
    }

    /**
     * Triggered on update.
     */
    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updated = new \DateTime('now');
    }

    /**
     * @return MemberTranslation[]
     */
    public function getComments(): array
    {
        return $this->comments;
    }

    public function getComment(): ?int
    {
        return $this->comment;
    }

    /**
     * Sets the comment for the membership.
     */
    private function setComment($commentId)
    {
        $this->comment = $commentId;
    }
}
