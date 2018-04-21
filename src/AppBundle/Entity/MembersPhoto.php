<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MembersPhoto.
 *
 * @ORM\Table(name="membersphotos", indexes={@ORM\Index(name="IdMember", columns={"IdMember"})})
 * @ORM\Entity
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class MembersPhoto
{
    /**
     * @var string
     *
     * @ORM\Column(name="FilePath", type="text", length=255, nullable=false)
     */
    private $filepath;

    /**
     * @var int
     *
     * @ORM\Column(name="IdMember", type="integer", nullable=false)
     */
    private $idmember;

    /**
     * @var int
     *
     * @ORM\Column(name="SortOrder", type="smallint", nullable=false)
     */
    private $sortOrder = '0';

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
     * @var int
     *
     * @ORM\Column(name="Comment", type="integer", nullable=false)
     */
    private $comment;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Set filepath.
     *
     * @param string $filepath
     *
     * @return MembersPhoto
     */
    public function setFilepath($filepath)
    {
        $this->filepath = $filepath;

        return $this;
    }

    /**
     * Get filepath.
     *
     * @return string
     */
    public function getFilepath()
    {
        return $this->filepath;
    }

    /**
     * Set idmember.
     *
     * @param int $idmember
     *
     * @return MembersPhoto
     */
    public function setIdmember($idmember)
    {
        $this->idmember = $idmember;

        return $this;
    }

    /**
     * Get idmember.
     *
     * @return int
     */
    public function getIdmember()
    {
        return $this->idmember;
    }

    /**
     * Set sortorder.
     *
     * @param int $sortOrder
     *
     * @return MembersPhoto
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * Get sortorder.
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * Set updated.
     *
     * @param \DateTime $updated
     *
     * @return MembersPhoto
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return MembersPhoto
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set comment.
     *
     * @param int $comment
     *
     * @return MembersPhoto
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment.
     *
     * @return int
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
