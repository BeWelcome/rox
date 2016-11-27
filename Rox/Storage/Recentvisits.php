<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Recentvisits
 *
 * @ORM\Table(name="recentvisits", indexes={@ORM\Index(name="IdMember", columns={"IdMember"}), @ORM\Index(name="IdVisitor", columns={"IdVisitor"})})
 * @ORM\Entity
 */
class Recentvisits
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
     * @ORM\Column(name="IdVisitor", type="integer", nullable=false)
     */
    private $idvisitor;

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
     * @return Recentvisits
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
     * @return Recentvisits
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

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Recentvisits
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
