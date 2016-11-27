<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Broadcast
 *
 * @ORM\Table(name="broadcast")
 * @ORM\Entity
 */
class Broadcast
{
    /**
     * @var integer
     *
     * @ORM\Column(name="IdCreator", type="integer", nullable=false)
     */
    private $idcreator;

    /**
     * @var string
     *
     * @ORM\Column(name="Name", type="text", length=65535, nullable=false)
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created = 'CURRENT_TIMESTAMP';

    /**
     * @var string
     *
     * @ORM\Column(name="Status", type="string", nullable=false)
     */
    private $status = 'Created';

    /**
     * @var string
     *
     * @ORM\Column(name="Type", type="string", nullable=true)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="EmailFrom", type="text", length=65535, nullable=true)
     */
    private $emailfrom;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set idcreator
     *
     * @param integer $idcreator
     *
     * @return Broadcast
     */
    public function setIdcreator($idcreator)
    {
        $this->idcreator = $idcreator;

        return $this;
    }

    /**
     * Get idcreator
     *
     * @return integer
     */
    public function getIdcreator()
    {
        return $this->idcreator;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Broadcast
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Broadcast
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
     * Set status
     *
     * @param string $status
     *
     * @return Broadcast
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
     * Set type
     *
     * @param string $type
     *
     * @return Broadcast
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
     * Set emailfrom
     *
     * @param string $emailfrom
     *
     * @return Broadcast
     */
    public function setEmailfrom($emailfrom)
    {
        $this->emailfrom = $emailfrom;

        return $this;
    }

    /**
     * Get emailfrom
     *
     * @return string
     */
    public function getEmailfrom()
    {
        return $this->emailfrom;
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
