<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Membersgroups.
 *
 * @ORM\Table(name="membersgroups", uniqueConstraints={@ORM\UniqueConstraint(name="UniqueIdMemberIdGroup", columns={"IdMember", "IdGroup"})}, indexes={@ORM\Index(name="IdGroup", columns={"IdGroup"}), @ORM\Index(name="IdMember", columns={"IdMember"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class GroupMembership
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var MembersTrad
     *
     * @ORM\ManyToMany(targetEntity="MembersTrad", fetch="LAZY")
     * @ORM\JoinTable(name="group_membership_trads",
     *      joinColumns={@ORM\JoinColumn(name="group_membership_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="members_trad_id", referencedColumnName="id", unique=true)}
     *      )
     */
    private $comments;

    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="Member", inversedBy="groupMemberships")
     * @ORM\JoinColumn(name="IdMember", referencedColumnName="id", nullable=FALSE)
     */
    private $member;

    /**
     * @var Group
     *
     * @ORM\ManyToOne(targetEntity="Group", inversedBy="groupMemberships")
     * @ORM\JoinColumn(name="IdGroup", referencedColumnName="id", nullable=FALSE)
     */
    private $group;

    /**
     * @var string
     *
     * @ORM\Column(name="Status", type="group_membership_status", nullable=false)
     */
    private $status = 'WantToBeIn';

    /**
     * @var string
     *
     * @ORM\Column(name="IacceptMassMailFromThisGroup", type="string", nullable=false)
     */
    private $iacceptmassmailfromthisgroup = 'no';

    /**
     * @var string
     *
     * @ORM\Column(name="CanSendGroupMessage", type="string", nullable=false)
     */
    private $cansendgroupmessage = 'yes';

    /**
     * @var bool
     *
     * @ORM\Column(name="notificationsEnabled", type="boolean", nullable=false)
     */
    private $notificationsenabled = '1';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
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
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set comment.
     *
     * @param int $comment
     *
     * @return GroupMembership
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment.
     *
     * @return int
     */
    public function getComment()
    {
        return $this->comment;
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
     * Set iacceptmassmailfromthisgroup.
     *
     * @param string $iacceptmassmailfromthisgroup
     *
     * @return GroupMembership
     */
    public function setIacceptmassmailfromthisgroup($iacceptmassmailfromthisgroup)
    {
        $this->iacceptmassmailfromthisgroup = $iacceptmassmailfromthisgroup;

        return $this;
    }

    /**
     * Get iacceptmassmailfromthisgroup.
     *
     * @return string
     */
    public function getIacceptmassmailfromthisgroup()
    {
        return $this->iacceptmassmailfromthisgroup;
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
     * @param MembersTrad $comment
     *
     * @return GroupMembership
     */
    public function addComment(MembersTrad $comment)
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
        }

        return $this;
    }

    /**
     * Remove a comment from the membership.
     *
     * @param MembersTrad $comment
     *
     * @return GroupMembership
     */
    public function removeComment(MembersTrad $comment)
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
     * Triggered on insert.
     *
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->created = new \DateTime('now');
    }

    /**
     * Triggered on update.
     *
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updated = new \DateTime('now');
    }

    /**
     * @return Collection|MembersTrad[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }
}
