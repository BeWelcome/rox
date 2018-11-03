<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Previousversion
 *
 * @ORM\Table(name="previousversion", indexes={@ORM\Index(name="IdMember", columns={"IdMember"})})
 * @ORM\Entity
 */
class Previousversion
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
     * @ORM\Column(name="TableName", type="text", length=255, nullable=false)
     */
    private $tablename;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdInTable", type="integer", nullable=false)
     */
    private $idintable;

    /**
     * @var string
     *
     * @ORM\Column(name="Type", type="string", nullable=false)
     */
    private $type = 'DoneByMember';

    /**
     * @var string
     *
     * @ORM\Column(name="XmlOldVersion", type="text", length=65535, nullable=false)
     */
    private $xmloldversion;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created = 'CURRENT_TIMESTAMP';

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
     * @return Previousversion
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
     * Set tablename
     *
     * @param string $tablename
     *
     * @return Previousversion
     */
    public function setTablename($tablename)
    {
        $this->tablename = $tablename;

        return $this;
    }

    /**
     * Get tablename
     *
     * @return string
     */
    public function getTablename()
    {
        return $this->tablename;
    }

    /**
     * Set idintable
     *
     * @param integer $idintable
     *
     * @return Previousversion
     */
    public function setIdintable($idintable)
    {
        $this->idintable = $idintable;

        return $this;
    }

    /**
     * Get idintable
     *
     * @return integer
     */
    public function getIdintable()
    {
        return $this->idintable;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Previousversion
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
     * Set xmloldversion
     *
     * @param string $xmloldversion
     *
     * @return Previousversion
     */
    public function setXmloldversion($xmloldversion)
    {
        $this->xmloldversion = $xmloldversion;

        return $this;
    }

    /**
     * Get xmloldversion
     *
     * @return string
     */
    public function getXmloldversion()
    {
        return $this->xmloldversion;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Previousversion
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
