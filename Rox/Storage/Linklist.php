<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Linklist
 *
 * @ORM\Table(name="linklist", indexes={@ORM\Index(name="kkey", columns={"fromID", "toID"})})
 * @ORM\Entity
 */
class Linklist
{
    /**
     * @var integer
     *
     * @ORM\Column(name="fromID", type="integer", nullable=false)
     */
    private $fromid;

    /**
     * @var integer
     *
     * @ORM\Column(name="toID", type="integer", nullable=false)
     */
    private $toid;

    /**
     * @var boolean
     *
     * @ORM\Column(name="degree", type="boolean", nullable=false)
     */
    private $degree;

    /**
     * @var boolean
     *
     * @ORM\Column(name="rank", type="boolean", nullable=false)
     */
    private $rank;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=10000, nullable=false)
     */
    private $path;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated = 'CURRENT_TIMESTAMP';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set fromid
     *
     * @param integer $fromid
     *
     * @return Linklist
     */
    public function setFromid($fromid)
    {
        $this->fromid = $fromid;

        return $this;
    }

    /**
     * Get fromid
     *
     * @return integer
     */
    public function getFromid()
    {
        return $this->fromid;
    }

    /**
     * Set toid
     *
     * @param integer $toid
     *
     * @return Linklist
     */
    public function setToid($toid)
    {
        $this->toid = $toid;

        return $this;
    }

    /**
     * Get toid
     *
     * @return integer
     */
    public function getToid()
    {
        return $this->toid;
    }

    /**
     * Set degree
     *
     * @param boolean $degree
     *
     * @return Linklist
     */
    public function setDegree($degree)
    {
        $this->degree = $degree;

        return $this;
    }

    /**
     * Get degree
     *
     * @return boolean
     */
    public function getDegree()
    {
        return $this->degree;
    }

    /**
     * Set rank
     *
     * @param boolean $rank
     *
     * @return Linklist
     */
    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * Get rank
     *
     * @return boolean
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Set path
     *
     * @param string $path
     *
     * @return Linklist
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Linklist
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
