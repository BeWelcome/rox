<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Privilegescopes
 *
 * @ORM\Table(name="privilegescopes")
 * @ORM\Entity
 */
class Privilegescopes
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated = 'CURRENT_TIMESTAMP';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdMember", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idmember;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdRole", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idrole;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdPrivilege", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idprivilege;

    /**
     * @var string
     *
     * @ORM\Column(name="IdType", type="string", length=32)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idtype;



    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Privilegescopes
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
     * Set idmember
     *
     * @param integer $idmember
     *
     * @return Privilegescopes
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
     * Set idrole
     *
     * @param integer $idrole
     *
     * @return Privilegescopes
     */
    public function setIdrole($idrole)
    {
        $this->idrole = $idrole;

        return $this;
    }

    /**
     * Get idrole
     *
     * @return integer
     */
    public function getIdrole()
    {
        return $this->idrole;
    }

    /**
     * Set idprivilege
     *
     * @param integer $idprivilege
     *
     * @return Privilegescopes
     */
    public function setIdprivilege($idprivilege)
    {
        $this->idprivilege = $idprivilege;

        return $this;
    }

    /**
     * Get idprivilege
     *
     * @return integer
     */
    public function getIdprivilege()
    {
        return $this->idprivilege;
    }

    /**
     * Set idtype
     *
     * @param string $idtype
     *
     * @return Privilegescopes
     */
    public function setIdtype($idtype)
    {
        $this->idtype = $idtype;

        return $this;
    }

    /**
     * Get idtype
     *
     * @return string
     */
    public function getIdtype()
    {
        return $this->idtype;
    }
}
