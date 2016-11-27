<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Membersphotos
 *
 * @ORM\Table(name="membersphotos", indexes={@ORM\Index(name="IdMember", columns={"IdMember"})})
 * @ORM\Entity
 */
class Membersphotos
{
    /**
     * @var string
     *
     * @ORM\Column(name="FilePath", type="text", length=255, nullable=false)
     */
    private $filepath;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdMember", type="integer", nullable=false)
     */
    private $idmember;

    /**
     * @var boolean
     *
     * @ORM\Column(name="SortOrder", type="boolean", nullable=false)
     */
    private $sortorder = '0';

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
     * @ORM\Column(name="Comment", type="integer", nullable=false)
     */
    private $comment;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set filepath
     *
     * @param string $filepath
     *
     * @return Membersphotos
     */
    public function setFilepath($filepath)
    {
        $this->filepath = $filepath;

        return $this;
    }

    /**
     * Get filepath
     *
     * @return string
     */
    public function getFilepath()
    {
        return $this->filepath;
    }

    /**
     * Set idmember
     *
     * @param integer $idmember
     *
     * @return Membersphotos
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
     * Set sortorder
     *
     * @param boolean $sortorder
     *
     * @return Membersphotos
     */
    public function setSortorder($sortorder)
    {
        $this->sortorder = $sortorder;

        return $this;
    }

    /**
     * Get sortorder
     *
     * @return boolean
     */
    public function getSortorder()
    {
        return $this->sortorder;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Membersphotos
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
     * @return Membersphotos
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
     * Set comment
     *
     * @param integer $comment
     *
     * @return Membersphotos
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return integer
     */
    public function getComment()
    {
        return $this->comment;
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
