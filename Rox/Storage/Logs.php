<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Logs
 *
 * @ORM\Table(name="logs")
 * @ORM\Entity
 */
class Logs
{
    /**
     * @var integer
     *
     * @ORM\Column(name="IdMember", type="integer", nullable=false)
     */
    private $idmember;

    /**
     * @var string
     *
     * @ORM\Column(name="Str", type="text", length=65535, nullable=false)
     */
    private $str;

    /**
     * @var string
     *
     * @ORM\Column(name="Type", type="text", length=255, nullable=false)
     */
    private $type;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created = 'CURRENT_TIMESTAMP';

    /**
     * @var integer
     *
     * @ORM\Column(name="IpAddress", type="integer", nullable=false)
     */
    private $ipaddress;

    /**
     * @var string
     *
     * @ORM\Column(name="DebugTracking", type="string", nullable=false)
     */
    private $debugtracking;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set idmember
     *
     * @param integer $idmember
     *
     * @return Logs
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
     * Set str
     *
     * @param string $str
     *
     * @return Logs
     */
    public function setStr($str)
    {
        $this->str = $str;

        return $this;
    }

    /**
     * Get str
     *
     * @return string
     */
    public function getStr()
    {
        return $this->str;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Logs
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
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Logs
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
     * Set ipaddress
     *
     * @param integer $ipaddress
     *
     * @return Logs
     */
    public function setIpaddress($ipaddress)
    {
        $this->ipaddress = $ipaddress;

        return $this;
    }

    /**
     * Get ipaddress
     *
     * @return integer
     */
    public function getIpaddress()
    {
        return $this->ipaddress;
    }

    /**
     * Set debugtracking
     *
     * @param string $debugtracking
     *
     * @return Logs
     */
    public function setDebugtracking($debugtracking)
    {
        $this->debugtracking = $debugtracking;

        return $this;
    }

    /**
     * Get debugtracking
     *
     * @return string
     */
    public function getDebugtracking()
    {
        return $this->debugtracking;
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
