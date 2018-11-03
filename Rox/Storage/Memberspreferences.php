<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Memberspreferences
 *
 * @ORM\Table(name="memberspreferences", indexes={@ORM\Index(name="IdMember", columns={"IdMember", "IdPreference"}), @ORM\Index(name="IdPreference", columns={"IdPreference"})})
 * @ORM\Entity
 */
class Memberspreferences
{
    /**
     * @var integer
     *
     * @ORM\Column(name="IdMember", type="integer", nullable=false)
     */
    private $idmember;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdPreference", type="integer", nullable=false)
     */
    private $idpreference;

    /**
     * @var string
     *
     * @ORM\Column(name="Value", type="text", length=65535, nullable=false)
     */
    private $value;

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
     * @return Memberspreferences
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
     * Set idpreference
     *
     * @param integer $idpreference
     *
     * @return Memberspreferences
     */
    public function setIdpreference($idpreference)
    {
        $this->idpreference = $idpreference;

        return $this;
    }

    /**
     * Get idpreference
     *
     * @return integer
     */
    public function getIdpreference()
    {
        return $this->idpreference;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return Memberspreferences
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Memberspreferences
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
     * @return Memberspreferences
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
