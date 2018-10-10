<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Membersgroups
 *
 * @ORM\Table(name="membersgroups", uniqueConstraints={@ORM\UniqueConstraint(name="UniqueIdMemberIdGroup", columns={"IdMember", "IdGroup"})}, indexes={@ORM\Index(name="IdGroup", columns={"IdGroup"}), @ORM\Index(name="IdMember", columns={"IdMember"})})
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity
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
    private $updated = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created = '0000-00-00 00:00:00';

    /**
     * @var integer
     *
     * @ORM\Column(name="Comment", type="integer", nullable=false)
     */
    private $comment;

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
     * @var boolean
     *
     * @ORM\Column(name="notificationsEnabled", type="boolean", nullable=false)
     */
    private $notificationsenabled = '1';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set updated
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
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set created
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
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set comment
     *
     * @param integer $comment
     *
     * @return GroupMembership
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return integer
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set member
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
     * Get member
     *
     * @return Member
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Set group
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
     * Get group
     *
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set status
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
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set iacceptmassmailfromthisgroup
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
     * Get iacceptmassmailfromthisgroup
     *
     * @return string
     */
    public function getIacceptmassmailfromthisgroup()
    {
        return $this->iacceptmassmailfromthisgroup;
    }

    /**
     * Set cansendgroupmessage
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
     * Get cansendgroupmessage
     *
     * @return string
     */
    public function getCansendgroupmessage()
    {
        return $this->cansendgroupmessage;
    }

    /**
     * Set notificationsenabled
     *
     * @param boolean $notificationsenabled
     *
     * @return GroupMembership
     */
    public function setNotificationsenabled($notificationsenabled)
    {
        $this->notificationsenabled = $notificationsenabled;

        return $this;
    }

    /**
     * Get notificationsenabled
     *
     * @return boolean
     */
    public function getNotificationsenabled()
    {
        return $this->notificationsenabled;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
