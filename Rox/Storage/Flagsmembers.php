<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Flagsmembers
 *
 * @ORM\Table(name="flagsmembers", indexes={@ORM\Index(name="IdMember", columns={"IdMember", "IdFlag"})})
 * @ORM\Entity
 */
class Flagsmembers
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
     * @ORM\Column(name="IdFlag", type="integer", nullable=false)
     */
    private $idflag;

    /**
     * @var integer
     *
     * @ORM\Column(name="Level", type="integer", nullable=false)
     */
    private $level = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="Scope", type="text", length=255, nullable=false)
     */
    private $scope;

    /**
     * @var string
     *
     * @ORM\Column(name="Comment", type="text", length=65535, nullable=false)
     */
    private $comment;

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
     * @return Flagsmembers
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
     * Set idflag
     *
     * @param integer $idflag
     *
     * @return Flagsmembers
     */
    public function setIdflag($idflag)
    {
        $this->idflag = $idflag;

        return $this;
    }

    /**
     * Get idflag
     *
     * @return integer
     */
    public function getIdflag()
    {
        return $this->idflag;
    }

    /**
     * Set level
     *
     * @param integer $level
     *
     * @return Flagsmembers
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return integer
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set scope
     *
     * @param string $scope
     *
     * @return Flagsmembers
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get scope
     *
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return Flagsmembers
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Flagsmembers
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
     * @return Flagsmembers
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
