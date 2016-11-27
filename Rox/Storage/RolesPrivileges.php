<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RolesPrivileges
 *
 * @ORM\Table(name="roles_privileges")
 * @ORM\Entity
 */
class RolesPrivileges
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
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return RolesPrivileges
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
     * Set idrole
     *
     * @param integer $idrole
     *
     * @return RolesPrivileges
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
     * @return RolesPrivileges
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
}
