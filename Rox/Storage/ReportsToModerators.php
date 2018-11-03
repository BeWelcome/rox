<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ReportsToModerators
 *
 * @ORM\Table(name="reports_to_moderators", indexes={@ORM\Index(name="IdReporter", columns={"IdReporter", "IdPost", "IdThread"})})
 * @ORM\Entity
 */
class ReportsToModerators
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
     * @var string
     *
     * @ORM\Column(name="PostComment", type="text", length=65535, nullable=false)
     */
    private $postcomment;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdReporter", type="integer", nullable=false)
     */
    private $idreporter;

    /**
     * @var string
     *
     * @ORM\Column(name="ModeratorComment", type="text", length=65535, nullable=false)
     */
    private $moderatorcomment;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdModerator", type="integer", nullable=false)
     */
    private $idmoderator;

    /**
     * @var string
     *
     * @ORM\Column(name="Status", type="string", nullable=false)
     */
    private $status = 'Open';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdPost", type="integer", nullable=false)
     */
    private $idpost;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdThread", type="integer", nullable=false)
     */
    private $idthread;

    /**
     * @var string
     *
     * @ORM\Column(name="Type", type="string", nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="LastWhoSpoke", type="string", nullable=false)
     */
    private $lastwhospoke = 'Member';

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
     * @return ReportsToModerators
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
     * @return ReportsToModerators
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
     * Set postcomment
     *
     * @param string $postcomment
     *
     * @return ReportsToModerators
     */
    public function setPostcomment($postcomment)
    {
        $this->postcomment = $postcomment;

        return $this;
    }

    /**
     * Get postcomment
     *
     * @return string
     */
    public function getPostcomment()
    {
        return $this->postcomment;
    }

    /**
     * Set idreporter
     *
     * @param integer $idreporter
     *
     * @return ReportsToModerators
     */
    public function setIdreporter($idreporter)
    {
        $this->idreporter = $idreporter;

        return $this;
    }

    /**
     * Get idreporter
     *
     * @return integer
     */
    public function getIdreporter()
    {
        return $this->idreporter;
    }

    /**
     * Set moderatorcomment
     *
     * @param string $moderatorcomment
     *
     * @return ReportsToModerators
     */
    public function setModeratorcomment($moderatorcomment)
    {
        $this->moderatorcomment = $moderatorcomment;

        return $this;
    }

    /**
     * Get moderatorcomment
     *
     * @return string
     */
    public function getModeratorcomment()
    {
        return $this->moderatorcomment;
    }

    /**
     * Set idmoderator
     *
     * @param integer $idmoderator
     *
     * @return ReportsToModerators
     */
    public function setIdmoderator($idmoderator)
    {
        $this->idmoderator = $idmoderator;

        return $this;
    }

    /**
     * Get idmoderator
     *
     * @return integer
     */
    public function getIdmoderator()
    {
        return $this->idmoderator;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return ReportsToModerators
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
     * Set idpost
     *
     * @param integer $idpost
     *
     * @return ReportsToModerators
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
     * Set idthread
     *
     * @param integer $idthread
     *
     * @return ReportsToModerators
     */
    public function setIdthread($idthread)
    {
        $this->idthread = $idthread;

        return $this;
    }

    /**
     * Get idthread
     *
     * @return integer
     */
    public function getIdthread()
    {
        return $this->idthread;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return ReportsToModerators
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
     * Set lastwhospoke
     *
     * @param string $lastwhospoke
     *
     * @return ReportsToModerators
     */
    public function setLastwhospoke($lastwhospoke)
    {
        $this->lastwhospoke = $lastwhospoke;

        return $this;
    }

    /**
     * Get lastwhospoke
     *
     * @return string
     */
    public function getLastwhospoke()
    {
        return $this->lastwhospoke;
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
