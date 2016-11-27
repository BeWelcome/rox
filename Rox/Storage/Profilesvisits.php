<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Profilesvisits
 *
 * @ORM\Table(name="profilesvisits", indexes={@ORM\Index(name="IdVisitor", columns={"IdVisitor"})})
 * @ORM\Entity
 */
class Profilesvisits
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated = '0000-00-00 00:00:00';

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
     * @ORM\Column(name="IdVisitor", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idvisitor;



    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Profilesvisits
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
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Profilesvisits
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
     * @return Profilesvisits
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
     * Set idvisitor
     *
     * @param integer $idvisitor
     *
     * @return Profilesvisits
     */
    public function setIdvisitor($idvisitor)
    {
        $this->idvisitor = $idvisitor;

        return $this;
    }

    /**
     * Get idvisitor
     *
     * @return integer
     */
    public function getIdvisitor()
    {
        return $this->idvisitor;
    }
}
