<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PostsNotificationqueue
 *
 * @ORM\Table(name="posts_notificationqueue", indexes={@ORM\Index(name="IdxStatus", columns={"Status"})})
 * @ORM\Entity
 */
class PostsNotificationqueue
{
    /**
     * @var string
     *
     * @ORM\Column(name="Status", type="string", nullable=false)
     */
    private $status = 'ToSend';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdMember", type="integer", nullable=false)
     */
    private $idmember;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdPost", type="integer", nullable=false)
     */
    private $idpost;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated = '0000-00-00 00:00:00';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created = '0000-00-00 00:00:00';

    /**
     * @var string
     *
     * @ORM\Column(name="Type", type="string", nullable=false)
     */
    private $type = 'buggy';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdSubscription", type="integer", nullable=false)
     */
    private $idsubscription = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="TableSubscription", type="string", length=64, nullable=false)
     */
    private $tablesubscription = 'NotSet';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set status
     *
     * @param string $status
     *
     * @return PostsNotificationqueue
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
     * Set idmember
     *
     * @param integer $idmember
     *
     * @return PostsNotificationqueue
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
     * Set idpost
     *
     * @param integer $idpost
     *
     * @return PostsNotificationqueue
     */
    public function setIdpost($idpost)
    {
        $this->idpost = $idpost;

        return $this;
    }

    /**
     * Get idpost
     *
     * @return integer
     */
    public function getIdpost()
    {
        return $this->idpost;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return PostsNotificationqueue
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
     * @return PostsNotificationqueue
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
     * Set type
     *
     * @param string $type
     *
     * @return PostsNotificationqueue
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set idsubscription
     *
     * @param integer $idsubscription
     *
     * @return PostsNotificationqueue
     */
    public function setIdsubscription($idsubscription)
    {
        $this->idsubscription = $idsubscription;

        return $this;
    }

    /**
     * Get idsubscription
     *
     * @return integer
     */
    public function getIdsubscription()
    {
        return $this->idsubscription;
    }

    /**
     * Set tablesubscription
     *
     * @param string $tablesubscription
     *
     * @return PostsNotificationqueue
     */
    public function setTablesubscription($tablesubscription)
    {
        $this->tablesubscription = $tablesubscription;

        return $this;
    }

    /**
     * Get tablesubscription
     *
     * @return string
     */
    public function getTablesubscription()
    {
        return $this->tablesubscription;
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
