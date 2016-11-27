<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Broadcastmessages
 *
 * @ORM\Table(name="broadcastmessages")
 * @ORM\Entity
 */
class Broadcastmessages
{
    /**
     * @var integer
     *
     * @ORM\Column(name="IdEnqueuer", type="integer", nullable=false)
     */
    private $idenqueuer;

    /**
     * @var string
     *
     * @ORM\Column(name="Status", type="string", nullable=false)
     */
    private $status = 'ToApprove';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated = 'CURRENT_TIMESTAMP';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdBroadcast", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idbroadcast;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdReceiver", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idreceiver;



    /**
     * Set idenqueuer
     *
     * @param integer $idenqueuer
     *
     * @return Broadcastmessages
     */
    public function setIdenqueuer($idenqueuer)
    {
        $this->idenqueuer = $idenqueuer;

        return $this;
    }

    /**
     * Get idenqueuer
     *
     * @return integer
     */
    public function getIdenqueuer()
    {
        return $this->idenqueuer;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Broadcastmessages
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
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Broadcastmessages
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
     * Set idbroadcast
     *
     * @param integer $idbroadcast
     *
     * @return Broadcastmessages
     */
    public function setIdbroadcast($idbroadcast)
    {
        $this->idbroadcast = $idbroadcast;

        return $this;
    }

    /**
     * Get idbroadcast
     *
     * @return integer
     */
    public function getIdbroadcast()
    {
        return $this->idbroadcast;
    }

    /**
     * Set idreceiver
     *
     * @param integer $idreceiver
     *
     * @return Broadcastmessages
     */
    public function setIdreceiver($idreceiver)
    {
        $this->idreceiver = $idreceiver;

        return $this;
    }

    /**
     * Get idreceiver
     *
     * @return integer
     */
    public function getIdreceiver()
    {
        return $this->idreceiver;
    }
}
