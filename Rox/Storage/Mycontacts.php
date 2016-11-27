<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Mycontacts
 *
 * @ORM\Table(name="mycontacts")
 * @ORM\Entity
 */
class Mycontacts
{
    /**
     * @var integer
     *
     * @ORM\Column(name="Idmember", type="integer", nullable=false)
     */
    private $idmember;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdContact", type="integer", nullable=false)
     */
    private $idcontact;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated = 'CURRENT_TIMESTAMP';

    /**
     * @var string
     *
     * @ORM\Column(name="Category", type="text", length=255, nullable=false)
     */
    private $category;

    /**
     * @var string
     *
     * @ORM\Column(name="Comment", type="text", length=65535, nullable=false)
     */
    private $comment;

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
     * @return Mycontacts
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
     * Set idcontact
     *
     * @param integer $idcontact
     *
     * @return Mycontacts
     */
    public function setIdcontact($idcontact)
    {
        $this->idcontact = $idcontact;

        return $this;
    }

    /**
     * Get idcontact
     *
     * @return integer
     */
    public function getIdcontact()
    {
        return $this->idcontact;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Mycontacts
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
     * Set category
     *
     * @param string $category
     *
     * @return Mycontacts
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return Mycontacts
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
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Mycontacts
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
