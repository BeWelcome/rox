<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Rightsvolunteers
 *
 * @ORM\Table(name="rightsvolunteers", uniqueConstraints={@ORM\UniqueConstraint(name="IdMember", columns={"IdMember", "IdRight"})})
 * @ORM\Entity
 */
class Rightsvolunteers
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
     * @ORM\Column(name="IdRight", type="integer", nullable=false)
     */
    private $idright;

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
     * @return Rightsvolunteers
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
     * Set idright
     *
     * @param integer $idright
     *
     * @return Rightsvolunteers
     */
    public function setIdright($idright)
    {
        $this->idright = $idright;

        return $this;
    }

    /**
     * Get idright
     *
     * @return integer
     */
    public function getIdright()
    {
        return $this->idright;
    }

    /**
     * Set level
     *
     * @param integer $level
     *
     * @return Rightsvolunteers
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
     * @return Rightsvolunteers
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
     * @return Rightsvolunteers
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
     * @return Rightsvolunteers
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
     * @return Rightsvolunteers
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
