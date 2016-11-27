<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Membersgroups
 *
 * @ORM\Table(name="membersgroups", uniqueConstraints={@ORM\UniqueConstraint(name="UniqueIdMemberIdGroup", columns={"IdMember", "IdGroup"})}, indexes={@ORM\Index(name="IdGroup", columns={"IdGroup"}), @ORM\Index(name="IdMember", columns={"IdMember"})})
 * @ORM\Entity
 */
class Membersgroups
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
     * @var integer
     *
     * @ORM\Column(name="IdMember", type="integer", nullable=false)
     */
    private $idmember;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdGroup", type="integer", nullable=false)
     */
    private $idgroup;

    /**
     * @var string
     *
     * @ORM\Column(name="Status", type="string", nullable=false)
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
     * @return Membersgroups
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
     * @return Membersgroups
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
     * @return Membersgroups
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
     * Set idmember
     *
     * @param integer $idmember
     *
     * @return Membersgroups
     */
    public function setIdmember($idmember)
    {
        $this->idmember = $idmember;

        return $this;
    }

    /**
     * Get idmember
     *
     * @return integer
     */
    public function getIdmember()
    {
        return $this->idmember;
    }

    /**
     * Set idgroup
     *
     * @param integer $idgroup
     *
     * @return Membersgroups
     */
    public function setIdgroup($idgroup)
    {
        $this->idgroup = $idgroup;

        return $this;
    }

    /**
     * Get idgroup
     *
     * @return integer
     */
    public function getIdgroup()
    {
        return $this->idgroup;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Membersgroups
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
     * @return Membersgroups
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
     * @return Membersgroups
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
     * @return Membersgroups
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
