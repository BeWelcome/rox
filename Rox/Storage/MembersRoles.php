<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MembersRoles
 *
 * @ORM\Table(name="members_roles")
 * @ORM\Entity
 */
class MembersRoles
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
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return MembersRoles
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
     * @return MembersRoles
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
     * @return MembersRoles
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
}
